<?php
/**
 * SkillXchange Transaction Timeout Processor
 * 
 * This script should be run via cron job every hour:
 * 0 * * * * /usr/bin/php /path/to/process_transaction_timeouts.php
 * 
 * Or every 30 minutes for more responsive processing:
 * /30 * * * * /usr/bin/php /path/to/process_transaction_timeouts.php
 */

// Load application core
require_once dirname(__FILE__) . '/../app/bootstrap.php';

class TransactionTimeoutProcessor
{
    private $db;
    private $processedCount = 0;
    private $errors = [];

    public function __construct()
    {
        $this->db = new Database();
        $this->log("=== Transaction Timeout Processor Started ===");
    }

    private function ensureWalletExists($userId, $role = 'individual')
    {
        $this->db->query("SELECT id FROM wallets WHERE user_id = :user_id");
        $this->db->bind(':user_id', $userId);
        $wallet = $this->db->single();

        if (!$wallet) {
            $initialAmount = ($role === 'organization') ? 1000.00 : 250.00;
            $this->db->query("INSERT INTO wallets (user_id, balance) VALUES (:user_id, :balance)");
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':balance', $initialAmount);
            $this->db->execute();
        }
    }

    private function updateWalletBalance($userId, $amount, $operation = 'add')
    {
        if ($operation === 'subtract') {
            $this->db->query("UPDATE wallets SET balance = balance - :amount WHERE user_id = :user_id");
        } else {
            $this->db->query("UPDATE wallets SET balance = balance + :amount WHERE user_id = :user_id");
        }

        $this->db->bind(':amount', abs((float) $amount));
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }

    private function logWalletTransaction($senderId, $receiverId, $amount, $note, $transactionType = 'transfer')
    {
        $this->db->query("
            INSERT INTO wallet_transactions (sender_id, receiver_id, amount, note, transaction_type, status, created_at)
            VALUES (:sender_id, :receiver_id, :amount, :note, :transaction_type, 'completed', NOW())
        ");
        $this->db->bind(':sender_id', $senderId);
        $this->db->bind(':receiver_id', $receiverId);
        $this->db->bind(':amount', abs((float) $amount));
        $this->db->bind(':note', $note);
        $this->db->bind(':transaction_type', $transactionType);
        $this->db->execute();
    }

    /**
     * Main execution method
     */
    public function run()
    {
        try {
            // Process all timeout types
            $this->processTeacherTimeouts();
            $this->processLearnerTimeouts();
            $this->sendReminderNotifications();
            $this->sendExpiryWarnings();

            $this->log("=== Processing Complete ===");
            $this->log("Total events processed: {$this->processedCount}");
            
            if (!empty($this->errors)) {
                $this->log("ERRORS encountered: " . count($this->errors));
                foreach ($this->errors as $error) {
                    $this->log("ERROR: {$error}");
                }
            }

        } catch (Exception $e) {
            $this->log("FATAL ERROR: " . $e->getMessage());
            $this->log($e->getTraceAsString());
        }
    }

    // ============================================
    // 1. TEACHER TIMEOUT PROCESSING
    // Teacher didn't mark completed before expires_at
    // ============================================

    private function processTeacherTimeouts()
    {
        $this->log("--- Processing Teacher Timeouts ---");

        // Find events where teacher didn't complete in time
        $this->db->query("
            SELECT * FROM chat_transaction_events 
            WHERE status = 'active' 
            AND expires_at < NOW()
            ORDER BY expires_at ASC
        ");

        $events = $this->db->resultSet();
        $count = count($events);

        $this->log("Found {$count} teacher timeout events");

        foreach ($events as $event) {
            try {
                $this->processTeacherTimeout($event);
                $this->processedCount++;
            } catch (Exception $e) {
                $this->errors[] = "Teacher timeout event #{$event->id}: " . $e->getMessage();
            }
        }
    }

    private function processTeacherTimeout($event)
    {
        $this->log("Processing teacher timeout for event #{$event->id}");

        $this->db->beginTransaction();

        try {
            // Update event status to 'expired'
            $this->db->query("
                UPDATE chat_transaction_events 
                SET status = 'expired', 
                    updated_at = NOW()
                WHERE id = :event_id
            ");
            $this->db->bind(':event_id', $event->id);
            $this->db->execute();

            // Release BuckX if applicable
            if ($event->payment_type === 'buckx') {
                $this->releaseFrozenBuckX($event->id, $event->learner_id);
                $this->log("Released BuckX for event #{$event->id}");
            }

            // Void SkillX debt if applicable
            if ($event->payment_type === 'skillx') {
                $this->voidSkillDebt($event->id);
                $this->log("Voided SkillX debt for event #{$event->id}");
            }

            $this->db->commit();

            // Send notifications
            $this->createNotification(
                $event->id, 
                $event->teacher_id, 
                'expiry_warning',
                'Your session expired because it was not completed on time. No payment was transferred.'
            );

            $this->createNotification(
                $event->id, 
                $event->learner_id, 
                'payment_released',
                'The session expired. Your payment has been released back to you.'
            );

            $this->log("Teacher timeout processed successfully for event #{$event->id}");

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ============================================
    // 2. LEARNER TIMEOUT PROCESSING
    // Learner didn't verify within 7 days after teacher completed
    // ============================================

    private function processLearnerTimeouts()
    {
        $this->log("--- Processing Learner Timeouts ---");

        // Find events where learner didn't respond within 7 days
        $this->db->query("
            SELECT * FROM chat_transaction_events 
            WHERE status = 'teacher_completed' 
            AND teacher_completed_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY teacher_completed_at ASC
        ");

        $events = $this->db->resultSet();
        $count = count($events);

        $this->log("Found {$count} learner timeout events");

        foreach ($events as $event) {
            try {
                $this->processLearnerTimeout($event);
                $this->processedCount++;
            } catch (Exception $e) {
                $this->errors[] = "Learner timeout event #{$event->id}: " . $e->getMessage();
            }
        }
    }

    private function processLearnerTimeout($event)
    {
        $this->log("Processing learner timeout for event #{$event->id}");

        $this->db->beginTransaction();

        try {
            // Update event status to 'learner_timeout'
            $this->db->query("
                UPDATE chat_transaction_events 
                SET status = 'learner_timeout', 
                    auto_completed_at = NOW(),
                    updated_at = NOW()
                WHERE id = :event_id
            ");
            $this->db->bind(':event_id', $event->id);
            $this->db->execute();

            // Transfer BuckX if applicable
            if ($event->payment_type === 'buckx') {
                $this->transferBuckX($event->id, $event->learner_id, $event->teacher_id, $event->amount);
                $this->log("Transferred BuckX for event #{$event->id}");
            }

            // Activate SkillX debt if applicable
            if ($event->payment_type === 'skillx') {
                $this->activateSkillDebt($event->id, $event->learner_id, $event->skill_debt_hours);
                $this->log("Activated SkillX debt for event #{$event->id}");
            }

            $this->db->commit();

            // Send notifications
            $this->createNotification(
                $event->id, 
                $event->teacher_id, 
                'payment_transferred',
                'The learner did not respond within 7 days. Payment has been automatically transferred to you.'
            );

            $this->createNotification(
                $event->id, 
                $event->learner_id, 
                'timeout_warning',
                'You did not verify the session within 7 days. Payment was automatically transferred to the teacher.'
            );

            $this->log("Learner timeout processed successfully for event #{$event->id}");

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ============================================
    // 3. REMINDER NOTIFICATIONS
    // Send reminders for incomplete events
    // ============================================

    private function sendReminderNotifications()
    {
        $this->log("--- Sending Reminder Notifications ---");

        // Find active events that are halfway through their timeframe
        $this->db->query("
            SELECT e.*, 
                   TIMESTAMPDIFF(HOUR, e.both_agreed_at, NOW()) as hours_elapsed,
                   e.agreed_timeframe_hours as total_hours
            FROM chat_transaction_events e
            WHERE e.status = 'active'
            AND TIMESTAMPDIFF(HOUR, e.both_agreed_at, NOW()) >= (e.agreed_timeframe_hours / 2)
            AND NOT EXISTS (
                SELECT 1 FROM transaction_notifications tn
                WHERE tn.event_id = e.id 
                AND tn.type = 'reminder_incomplete'
                AND tn.sent = TRUE
            )
        ");

        $events = $this->db->resultSet();
        $count = count($events);

        $this->log("Found {$count} events needing reminders");

        foreach ($events as $event) {
            try {
                $hoursRemaining = $event->total_hours - $event->hours_elapsed;
                
                $this->createNotification(
                    $event->id,
                    $event->teacher_id,
                    'reminder_incomplete',
                    "Reminder: You have approximately {$hoursRemaining} hours remaining to complete this session."
                );

                $this->createNotification(
                    $event->id,
                    $event->learner_id,
                    'reminder_incomplete',
                    "Reminder: The session has approximately {$hoursRemaining} hours remaining."
                );

                $this->log("Sent reminder for event #{$event->id}");
                $this->processedCount++;

            } catch (Exception $e) {
                $this->errors[] = "Reminder event #{$event->id}: " . $e->getMessage();
            }
        }
    }

    // ============================================
    // 4. EXPIRY WARNING NOTIFICATIONS
    // Send warnings 24 hours before expiry
    // ============================================

    private function sendExpiryWarnings()
    {
        $this->log("--- Sending Expiry Warnings ---");

        // Find events expiring in the next 24 hours
        $this->db->query("
            SELECT e.*
            FROM chat_transaction_events e
            WHERE e.status = 'active'
            AND e.expires_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)
            AND NOT EXISTS (
                SELECT 1 FROM transaction_notifications tn
                WHERE tn.event_id = e.id 
                AND tn.type = 'expiry_warning'
                AND tn.sent = TRUE
            )
        ");

        $events = $this->db->resultSet();
        $count = count($events);

        $this->log("Found {$count} events expiring soon");

        foreach ($events as $event) {
            try {
                $expiryTime = new DateTime($event->expires_at);
                $now = new DateTime();
                $diff = $now->diff($expiryTime);
                $hoursLeft = $diff->h + ($diff->days * 24);

                $this->createNotification(
                    $event->id,
                    $event->teacher_id,
                    'expiry_warning',
                    "Warning: This session will expire in approximately {$hoursLeft} hours! Please mark it as completed."
                );

                $this->log("Sent expiry warning for event #{$event->id}");
                $this->processedCount++;

            } catch (Exception $e) {
                $this->errors[] = "Expiry warning event #{$event->id}: " . $e->getMessage();
            }
        }
    }

    // ============================================
    // HELPER FUNCTIONS
    // ============================================

    /**
     * Release frozen BuckX back to learner
     */
    private function releaseFrozenBuckX($eventId, $learnerId)
    {
        // Get frozen amount
        $this->db->query("SELECT amount FROM frozen_buckx WHERE event_id = :event_id AND status = 'frozen'");
        $this->db->bind(':event_id', $eventId);
        $frozen = $this->db->single();

        if (!$frozen) return;

        $amount = $frozen->amount;

        // Update frozen_buckx status
        $this->db->query("UPDATE frozen_buckx SET status = 'released', released_at = NOW() WHERE event_id = :event_id");
        $this->db->bind(':event_id', $eventId);
        $this->db->execute();

        // Update user's frozen balance
        $this->db->query("UPDATE users SET buckx_frozen = buckx_frozen - :amount WHERE id = :learner_id");
        $this->db->bind(':amount', $amount);
        $this->db->bind(':learner_id', $learnerId);
        $this->db->execute();

        // Log transaction
        $this->logTransaction($eventId, 'buckx_release', null, $learnerId, $amount, null, 
            "BuckX released due to teacher timeout for event #{$eventId}");
    }

    /**
     * Transfer BuckX from learner to teacher
     */
    private function transferBuckX($eventId, $learnerId, $teacherId, $amount)
    {
        $amount = (float) $amount;

        $this->ensureWalletExists($learnerId, 'individual');
        $this->ensureWalletExists($teacherId, 'individual');

        // Update frozen_buckx status
        $this->db->query("UPDATE frozen_buckx SET status = 'transferred', transferred_at = NOW() WHERE event_id = :event_id");
        $this->db->bind(':event_id', $eventId);
        $this->db->execute();

        // Deduct from learner's frozen balance tracker
        $this->db->query("
            UPDATE users 
            SET buckx_frozen = GREATEST(buckx_frozen - :amount, 0)
            WHERE id = :learner_id
        ");
        $this->db->bind(':amount', $amount);
        $this->db->bind(':learner_id', $learnerId);
        $this->db->execute();

        $this->updateWalletBalance($learnerId, $amount, 'subtract');
        $this->updateWalletBalance($teacherId, $amount, 'add');
        $this->logWalletTransaction(
            $learnerId,
            $teacherId,
            $amount,
            "BuckX transferred due to learner timeout for event #{$eventId}",
            'transfer'
        );

        // Log transaction
        $this->logTransaction($eventId, 'buckx_transfer', $learnerId, $teacherId, $amount, null, 
            "BuckX transferred due to learner timeout for event #{$eventId}");
    }

    /**
     * Void SkillX debt
     */
    private function voidSkillDebt($eventId)
    {
        $this->db->query("UPDATE skill_debt SET status = 'voided', voided_at = NOW() WHERE event_id = :event_id");
        $this->db->bind(':event_id', $eventId);
        $this->db->execute();

        $this->logTransaction($eventId, 'skillx_void', null, null, null, null, 
            "SkillX debt voided due to teacher timeout for event #{$eventId}");
    }

    /**
     * Activate SkillX debt
     */
    private function activateSkillDebt($eventId, $learnerId, $hours)
    {
        // Update debt status
        $this->db->query("UPDATE skill_debt SET status = 'active', activated_at = NOW() WHERE event_id = :event_id");
        $this->db->bind(':event_id', $eventId);
        $this->db->execute();

        // Update learner's total debt hours
        $this->db->query("UPDATE users SET skillx_debt_hours = skillx_debt_hours + :hours WHERE id = :learner_id");
        $this->db->bind(':hours', $hours);
        $this->db->bind(':learner_id', $learnerId);
        $this->db->execute();

        $this->logTransaction($eventId, 'skillx_transfer', null, null, null, $hours, 
            "SkillX debt activated due to learner timeout for event #{$eventId}");
    }

    /**
     * Create notification
     */
    private function createNotification($eventId, $userId, $type, $message)
    {
        // Insert into transaction_notifications
        $this->db->query("
            INSERT INTO transaction_notifications (event_id, user_id, type, message, sent, sent_at)
            VALUES (:event_id, :user_id, :type, :message, TRUE, NOW())
        ");
        $this->db->bind(':event_id', $eventId);
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':type', $type);
        $this->db->bind(':message', $message);
        $this->db->execute();

        // Also insert into main notifications table
        $titleMap = [
            'expiry_warning' => 'Session Expired',
            'payment_released' => 'Payment Released',
            'payment_transferred' => 'Payment Transferred',
            'timeout_warning' => 'Verification Timeout',
            'reminder' => 'Session Reminder',
        ];
        $title = $titleMap[$type] ?? 'Transaction Update';

        $this->db->query("
            INSERT INTO notifications (user_id, type, title, message, is_read, created_at)
            VALUES (:user_id, 'transaction', :title, :message, 0, NOW())
        ");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':title', $title);
        $this->db->bind(':message', $message);
        $this->db->execute();
    }

    /**
     * Log transaction history
     */
    private function logTransaction($eventId, $type, $fromUserId, $toUserId, $amount, $hours, $description)
    {
        $this->db->query("
            INSERT INTO transaction_history (event_id, type, from_user_id, to_user_id, amount, hours, description)
            VALUES (:event_id, :type, :from_user_id, :to_user_id, :amount, :hours, :description)
        ");
        $this->db->bind(':event_id', $eventId);
        $this->db->bind(':type', $type);
        $this->db->bind(':from_user_id', $fromUserId);
        $this->db->bind(':to_user_id', $toUserId);
        $this->db->bind(':amount', $amount);
        $this->db->bind(':hours', $hours);
        $this->db->bind(':description', $description);
        $this->db->execute();
    }

    /**
     * Log message with timestamp
     */
    private function log($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}\n";
        
        // Write to log file
        $logFile = dirname(__FILE__) . '/../logs/transaction_timeouts.log';
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        // Also output to console if running from CLI
        if (php_sapi_name() === 'cli') {
            echo $logMessage;
        }
    }
}

// ============================================
// EXECUTION
// ============================================

// Only run if executed directly (not included)
if (php_sapi_name() === 'cli' || !isset($_SERVER['HTTP_HOST'])) {
    $processor = new TransactionTimeoutProcessor();
    $processor->run();
} else {
    // Prevent web access
    http_response_code(403);
    die('This script can only be run from command line.');
}
?>
