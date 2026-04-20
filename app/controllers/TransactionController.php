<?php
// app/controllers/TransactionController.php


class TransactionController extends Controller
{
   private $db;


   public function __construct()
   {
       if (!isset($_SESSION['user_id'])) {
           header('Location: ' . URLROOT . '/auth/signin');
           exit();
       }
       $this->db = new Database();
   }

   private function ensureWalletExists($userId, $role = 'individual')
   {
       $walletModel = $this->model('Wallet');
       $walletModel->ensureWalletExists($userId, $role);
   }

   private function getWalletBalance($userId)
   {
       $this->db->query("SELECT balance FROM wallets WHERE user_id = :user_id");
       $this->db->bind(':user_id', $userId);
       $wallet = $this->db->single();
       return $wallet ? (float) $wallet->balance : 0.0;
   }

   private function getFrozenBuckx($userId)
   {
       $this->db->query("SELECT COALESCE(buckx_frozen, 0) AS buckx_frozen FROM users WHERE id = :user_id");
       $this->db->bind(':user_id', $userId);
       $user = $this->db->single();
       return $user ? (float) $user->buckx_frozen : 0.0;
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
       return $this->db->execute();
   }

   private function skillExists($skillName)
   {
       $this->db->query("SELECT id FROM skills WHERE LOWER(skill_name) = LOWER(:skill_name) LIMIT 1");
       $this->db->bind(':skill_name', trim($skillName));
       return (bool) $this->db->single();
   }

   private function getOffsettableSkillDebts($creditorId, $debtorId)
   {
       $this->db->query("
           SELECT *
           FROM skill_debt
           WHERE creditor_id = :creditor_id
             AND debtor_id = :debtor_id
             AND status = 'active'
           ORDER BY COALESCE(activated_at, created_at) ASC, id ASC
       ");
       $this->db->bind(':creditor_id', $creditorId);
       $this->db->bind(':debtor_id', $debtorId);
       return $this->db->resultSet() ?: [];
   }

   private function getEventSkillDebt($eventId)
   {
       $this->db->query("
           SELECT *
           FROM skill_debt
           WHERE event_id = :event_id
           ORDER BY id DESC
           LIMIT 1
       ");
       $this->db->bind(':event_id', $eventId);
       return $this->db->single();
   }

   private function recordSkillDebtSettlement($debtId, $eventId, $debtorId, $creditorId, $hoursApplied, $sourceSkillName, $settlementSkillName)
   {
       $this->db->query("
           INSERT INTO skill_debt_settlements
           (debt_id, event_id, debtor_id, creditor_id, hours_applied, source_skill_name, settlement_skill_name)
           VALUES (:debt_id, :event_id, :debtor_id, :creditor_id, :hours_applied, :source_skill_name, :settlement_skill_name)
       ");
       $this->db->bind(':debt_id', $debtId);
       $this->db->bind(':event_id', $eventId);
       $this->db->bind(':debtor_id', $debtorId);
       $this->db->bind(':creditor_id', $creditorId);
       $this->db->bind(':hours_applied', $hoursApplied);
       $this->db->bind(':source_skill_name', $sourceSkillName);
       $this->db->bind(':settlement_skill_name', $settlementSkillName);
       $this->db->execute();
   }

   private function settleSkillDebtForSession($event)
   {
       $eventDebt = $this->getEventSkillDebt($event->id);
       if (!$eventDebt) {
           throw new Exception('Missing skill debt record for this session.');
       }

       $sessionHours = (float)($event->skill_debt_hours ?? 0);
       $remainingHours = $sessionHours;
       $appliedHours = 0.0;

       foreach ($this->getOffsettableSkillDebts($event->learner_id, $event->teacher_id) as $offsetDebt) {
           if ($remainingHours <= 0) {
               break;
           }

           $availableHours = (float)($offsetDebt->hours_owed ?? 0);
           if ($availableHours <= 0) {
               continue;
           }

           $hoursToApply = min($availableHours, $remainingHours);
           $remainingOnDebt = $availableHours - $hoursToApply;

           if ($remainingOnDebt <= 0.00001) {
               $this->db->query("
                   UPDATE skill_debt
                   SET hours_owed = 0,
                       status = 'fulfilled',
                       fulfilled_at = NOW()
                   WHERE id = :debt_id
               ");
               $this->db->bind(':debt_id', $offsetDebt->id);
               $this->db->execute();
           } else {
               $this->db->query("
                   UPDATE skill_debt
                   SET hours_owed = :hours_owed
                   WHERE id = :debt_id
               ");
               $this->db->bind(':hours_owed', $remainingOnDebt);
               $this->db->bind(':debt_id', $offsetDebt->id);
               $this->db->execute();
           }

           $this->db->query("
               UPDATE users
               SET skillx_debt_hours = GREATEST(skillx_debt_hours - :hours, 0)
               WHERE id = :debtor_id
           ");
           $this->db->bind(':hours', $hoursToApply);
           $this->db->bind(':debtor_id', $event->teacher_id);
           $this->db->execute();

           $this->recordSkillDebtSettlement(
               (int)$offsetDebt->id,
               (int)$event->id,
               (int)$event->teacher_id,
               (int)$event->learner_id,
               $hoursToApply,
               $offsetDebt->skill_name ?? null,
               $event->skill_name ?? null
           );

           $appliedHours += $hoursToApply;
           $remainingHours -= $hoursToApply;
       }

       if ($remainingHours > 0.00001) {
           $this->db->query("
               UPDATE skill_debt
               SET hours_owed = :hours_owed,
                   status = 'active',
                   activated_at = NOW()
               WHERE id = :debt_id
           ");
           $this->db->bind(':hours_owed', $remainingHours);
           $this->db->bind(':debt_id', $eventDebt->id);
           $this->db->execute();

           $this->db->query("
               UPDATE users
               SET skillx_debt_hours = skillx_debt_hours + :hours
               WHERE id = :learner_id
           ");
           $this->db->bind(':hours', $remainingHours);
           $this->db->bind(':learner_id', $event->learner_id);
           $this->db->execute();
       } else {
           $this->db->query("
               UPDATE skill_debt
               SET status = 'fulfilled',
                   activated_at = NOW(),
                   fulfilled_at = NOW()
               WHERE id = :debt_id
           ");
           $this->db->bind(':debt_id', $eventDebt->id);
           $this->db->execute();
       }

       return [
           'applied_hours' => $appliedHours,
           'remaining_hours' => max(0, $remainingHours),
       ];
   }


   // ============================================
   // 1. CREATE TRANSACTION OFFER
   // ============================================
  
   /**
    * Create a new transaction offer in chat
    * POST /transaction/createOffer
    */
   public function createOffer()
   {
       header('Content-Type: application/json');


       if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
           echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
           return;
       }


       $userId = $_SESSION['user_id'];
       $chatId = (int)($_POST['chat_id'] ?? 0);
       $paymentType = trim($_POST['payment_type'] ?? ''); // 'buckx' or 'skillx'
       $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : null;
       $skillName = trim($_POST['skill_name'] ?? '');
       $timeframeHours = (int)($_POST['timeframe_hours'] ?? 0);
       $role = trim($_POST['role'] ?? ''); // 'teacher' or 'learner'
       $skillDebtHours = ($paymentType === 'skillx' && $timeframeHours > 0) ? (float) $timeframeHours : null;


       // Validation
       if (!$chatId || !in_array($paymentType, ['buckx', 'skillx']) || !$timeframeHours || !in_array($role, ['teacher', 'learner'])) {
           echo json_encode(['success' => false, 'message' => 'Missing or invalid parameters.']);
           return;
       }


       if ($paymentType === 'buckx' && (!$amount || $amount <= 0)) {
           echo json_encode(['success' => false, 'message' => 'Invalid BuckX amount.']);
           return;
       }


       if ($paymentType === 'skillx' && (!$skillDebtHours || $skillDebtHours <= 0)) {
           echo json_encode(['success' => false, 'message' => 'Invalid SkillX hours.']);
           return;
       }

       if ($paymentType === 'skillx' && $skillName === '') {
           echo json_encode(['success' => false, 'message' => 'Please select a skill for SkillX debt.']);
           return;
       }

       if ($paymentType === 'skillx' && !$this->skillExists($skillName)) {
           echo json_encode(['success' => false, 'message' => 'Selected skill is not available.']);
           return;
       }


       // Get chat participants
       $this->db->query("SELECT user1_id, user2_id FROM chats WHERE id = :chat_id");
       $this->db->bind(':chat_id', $chatId);
       $chat = $this->db->single();


       if (!$chat) {
           echo json_encode(['success' => false, 'message' => 'Chat not found.']);
           return;
       }


       // Verify user is part of this chat
       if ($chat->user1_id != $userId && $chat->user2_id != $userId) {
           echo json_encode(['success' => false, 'message' => 'Access denied.']);
           return;
       }


       // Determine teacher and learner
       $partnerId = ($chat->user1_id == $userId) ? $chat->user2_id : $chat->user1_id;
      
       if ($role === 'teacher') {
           $teacherId = $userId;
           $learnerId = $partnerId;
           $initialStatus = 'pending_learner';
       } else {
           $teacherId = $partnerId;
           $learnerId = $userId;
           $initialStatus = 'pending_teacher';
       }


       // Check for existing pending offers in this chat
       $this->db->query("
           SELECT id FROM chat_transaction_events
           WHERE chat_id = :chat_id
           AND status IN ('pending_learner', 'pending_teacher', 'active', 'teacher_completed')
       ");
       $this->db->bind(':chat_id', $chatId);
       if ($this->db->single()) {
           echo json_encode(['success' => false, 'message' => 'There is already an active transaction in this chat.']);
           return;
       }



       // Create transaction event
       $this->db->query("
           INSERT INTO chat_transaction_events
           (chat_id, teacher_id, learner_id, payment_type, amount, skill_debt_hours, skill_name, agreed_timeframe_hours, status)
           VALUES (:chat_id, :teacher_id, :learner_id, :payment_type, :amount, :skill_debt_hours, :skill_name, :timeframe_hours, :status)
       ");
       $this->db->bind(':chat_id', $chatId);
       $this->db->bind(':teacher_id', $teacherId);
       $this->db->bind(':learner_id', $learnerId);
       $this->db->bind(':payment_type', $paymentType);
       $this->db->bind(':amount', $amount);
       $this->db->bind(':skill_debt_hours', $skillDebtHours);
       $this->db->bind(':skill_name', $skillName);
       $this->db->bind(':timeframe_hours', $timeframeHours);
       $this->db->bind(':status', $initialStatus);


       if ($this->db->execute()) {
           $eventId = $this->db->lastInsertId();


           // Create notification for the other party
           $this->createNotification($eventId, $partnerId, 'offer_received',
               'You have received a new transaction offer in your chat.');


           echo json_encode([
               'success' => true,
               'message' => 'Offer created successfully.',
               'event_id' => $eventId
           ]);
       } else {
           echo json_encode(['success' => false, 'message' => 'Failed to create offer.']);
       }
   }


   // ============================================
   // 2. ACCEPT/REJECT OFFER
   // ============================================


   /**
    * Accept or reject a transaction offer
    * POST /transaction/respondToOffer
    */
   public function respondToOffer()
   {
       header('Content-Type: application/json');


       if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
           echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
           return;
       }


       $userId = $_SESSION['user_id'];
       $eventId = (int)($_POST['event_id'] ?? 0);
       $action = trim($_POST['action'] ?? ''); // 'accept' or 'reject'


       if (!$eventId || !in_array($action, ['accept', 'reject'])) {
           echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
           return;
       }


       // Get event
       $this->db->query("SELECT * FROM chat_transaction_events WHERE id = :event_id");
       $this->db->bind(':event_id', $eventId);
       $event = $this->db->single();


       if (!$event) {
           echo json_encode(['success' => false, 'message' => 'Event not found.']);
           return;
       }

       if (!in_array($event->status, ['pending_learner', 'pending_teacher'], true)) {
           echo json_encode([
               'success' => false,
               'message' => 'This offer has already been processed. Please refresh the chat.'
           ]);
           return;
       }


       // Verify user is the one who needs to respond
       if ($event->status === 'pending_learner' && $event->learner_id != $userId) {
           echo json_encode(['success' => false, 'message' => 'Access denied.']);
           return;
       }
       if ($event->status === 'pending_teacher' && $event->teacher_id != $userId) {
           echo json_encode(['success' => false, 'message' => 'Access denied.']);
           return;
       }


       if ($action === 'reject') {
           // Delete the offer
           $this->db->query("DELETE FROM chat_transaction_events WHERE id = :event_id");
           $this->db->bind(':event_id', $eventId);
           $this->db->execute();


           $partnerId = ($userId == $event->teacher_id) ? $event->learner_id : $event->teacher_id;
           $this->createNotification($eventId, $partnerId, 'offer_received', 'Your transaction offer was rejected.');


           echo json_encode(['success' => true, 'message' => 'Offer rejected.']);
           return;
       }


       // ACCEPT - Both parties have now agreed
       $this->db->beginTransaction();


       try {
           if ($event->payment_type === 'buckx') {
               $this->ensureWalletExists($event->learner_id, 'individual');
               $this->ensureWalletExists($event->teacher_id, 'individual');

               $walletBalance = $this->getWalletBalance($event->learner_id);
               $frozenBuckx = $this->getFrozenBuckx($event->learner_id);
               $availableBuckx = $walletBalance - $frozenBuckx;

               if ($availableBuckx < (float) $event->amount) {
                   throw new Exception('Learner does not have enough available BuckX for this session.');
               }
           }

           // Update event status to 'active'
           $expiresAt = date('Y-m-d H:i:s', strtotime("+{$event->agreed_timeframe_hours} hours"));
          
           $this->db->query("
               UPDATE chat_transaction_events
               SET status = 'active',
                   both_agreed_at = NOW(),
                   expires_at = :expires_at
               WHERE id = :event_id
           ");
           $this->db->bind(':expires_at', $expiresAt);
           $this->db->bind(':event_id', $eventId);
           $this->db->execute();
           
           

           // Handle BuckX freezing
           if ($event->payment_type === 'buckx') {
               // Freeze BuckX
               $this->db->query("
                   INSERT INTO frozen_buckx (event_id, user_id, amount, status)
                   VALUES (:event_id, :user_id, :amount, 'frozen')
               ");
               $this->db->bind(':event_id', $eventId);
               $this->db->bind(':user_id', $event->learner_id);
               $this->db->bind(':amount', $event->amount);
               $this->db->execute();


               // Update user's frozen balance
               $this->db->query("
                   UPDATE users
                   SET buckx_frozen = buckx_frozen + :amount
                   WHERE id = :learner_id
               ");
               $this->db->bind(':amount', $event->amount);
               $this->db->bind(':learner_id', $event->learner_id);
               $this->db->execute();


               // Transaction history
               $this->logTransaction($eventId, 'buckx_freeze', $event->learner_id, null, $event->amount, null,
                   "BuckX frozen for transaction #{$eventId}");
           }


           // Handle SkillX debt creation
           if ($event->payment_type === 'skillx') {
               $this->db->query("
                   INSERT INTO skill_debt (event_id, debtor_id, creditor_id, hours_owed, skill_name, status)
                   VALUES (:event_id, :debtor_id, :creditor_id, :hours, :skill_name, 'pending')
               ");
               $this->db->bind(':event_id', $eventId);
               $this->db->bind(':debtor_id', $event->learner_id);
               $this->db->bind(':creditor_id', $event->teacher_id);
               $this->db->bind(':hours', $event->skill_debt_hours);
               $this->db->bind(':skill_name', $event->skill_name);
               $this->db->execute();


               // Transaction history
               $this->logTransaction($eventId, 'skillx_create', $event->learner_id, $event->teacher_id,
                   null, $event->skill_debt_hours, "SkillX debt created for transaction #{$eventId}");
           }


           $this->db->commit();


           // Notifications
           $this->createNotification($eventId, $event->teacher_id, 'session_started',
               'Your transaction session has started!');
           $this->createNotification($eventId, $event->learner_id, 'session_started',
               'Your transaction session has started!');


           echo json_encode(['success' => true, 'message' => 'Transaction started successfully!']);


       } catch (\Throwable $e) {
           if (method_exists($this->db, 'inTransaction') && $this->db->inTransaction()) {
               $this->db->rollBack();
           }
           error_log("Transaction accept error: " . $e->getMessage());
           echo json_encode(['success' => false, 'message' => $e->getMessage() ?: 'Failed to start transaction.']);
       }
   }


   // ============================================
   // 3. LEAVE LESSON (TERMINATE)
   // ============================================


   /**
    * Terminate an active transaction
    * POST /transaction/leaveLesson
    */
   public function leaveLesson()
   {
       header('Content-Type: application/json');


       if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
           echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
           return;
       }


       $userId = $_SESSION['user_id'];
       $eventId = (int)($_POST['event_id'] ?? 0);


       if (!$eventId) {
           echo json_encode(['success' => false, 'message' => 'Invalid event ID.']);
           return;
       }


       // Get event
       $this->db->query("SELECT * FROM chat_transaction_events WHERE id = :event_id");
       $this->db->bind(':event_id', $eventId);
       $event = $this->db->single();


       if (!$event) {
           echo json_encode(['success' => false, 'message' => 'Event not found.']);
           return;
       }


       // Verify user is part of this transaction
       if ($event->teacher_id != $userId && $event->learner_id != $userId) {
           echo json_encode(['success' => false, 'message' => 'Access denied.']);
           return;
       }


       // Can only terminate if status is 'active' or 'pending'
       if (!in_array($event->status, ['pending_learner', 'pending_teacher', 'active'])) {
           echo json_encode(['success' => false, 'message' => 'Cannot terminate this transaction.']);
           return;
       }


       $this->db->beginTransaction();


       try {
           // Update event status
           $this->db->query("
               UPDATE chat_transaction_events
               SET status = 'terminated',
                   terminated_by = :user_id,
                   terminated_at = NOW()
               WHERE id = :event_id
           ");
           $this->db->bind(':user_id', $userId);
           $this->db->bind(':event_id', $eventId);
           $this->db->execute();


           // Release BuckX if frozen
           if ($event->payment_type === 'buckx' && $event->status === 'active') {
               $this->releaseFrozenBuckX($eventId, $event->learner_id);
           }


           // Void SkillX debt if pending
           if ($event->payment_type === 'skillx' && $event->status === 'active') {
               $this->voidSkillDebt($eventId);
           }


           $this->db->commit();


           // Notifications
           $partnerId = ($userId == $event->teacher_id) ? $event->learner_id : $event->teacher_id;
           $this->createNotification($eventId, $partnerId, 'offer_received',
               'The transaction has been terminated.');


           echo json_encode(['success' => true, 'message' => 'Transaction terminated successfully.']);


       } catch (Exception $e) {
           $this->db->rollBack();
           error_log("Leave lesson error: " . $e->getMessage());
           echo json_encode(['success' => false, 'message' => 'Failed to terminate transaction.']);
       }
   }


   // ============================================
   // 4. TEACHER MARKS COMPLETED
   // ============================================


   /**
    * Teacher marks session as completed
    * POST /transaction/markCompleted
    */
   public function markCompleted()
   {
       header('Content-Type: application/json');


       if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
           echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
           return;
       }


       $userId = $_SESSION['user_id'];
       $eventId = (int)($_POST['event_id'] ?? 0);


       if (!$eventId) {
           echo json_encode(['success' => false, 'message' => 'Invalid event ID.']);
           return;
       }


       // Get event
       $this->db->query("SELECT * FROM chat_transaction_events WHERE id = :event_id");
       $this->db->bind(':event_id', $eventId);
       $event = $this->db->single();


       if (!$event) {
           echo json_encode(['success' => false, 'message' => 'Event not found.']);
           return;
       }


       // Verify user is the teacher
       if ($event->teacher_id != $userId) {
           echo json_encode(['success' => false, 'message' => 'Only the teacher can mark as completed.']);
           return;
       }


       // Must be in 'active' status
       if ($event->status !== 'active') {
           echo json_encode(['success' => false, 'message' => 'Transaction is not active.']);
           return;
       }


       // Update status
       $this->db->query("
           UPDATE chat_transaction_events
           SET status = 'teacher_completed',
               teacher_completed_at = NOW()
           WHERE id = :event_id
       ");
       $this->db->bind(':event_id', $eventId);
      
       if ($this->db->execute()) {
           // Notification to learner
           $this->createNotification($eventId, $event->learner_id, 'teacher_completed',
               'The teacher has marked the session as completed. Please verify.');


           echo json_encode(['success' => true, 'message' => 'Marked as completed. Waiting for learner verification.']);
       } else {
           echo json_encode(['success' => false, 'message' => 'Failed to update status.']);
       }
   }


   // ============================================
   // 5. LEARNER VERIFICATION (AGREED/REPORT)
   // ============================================


   /**
    * Learner verifies completion or reports issue
    * POST /transaction/verifyCompletion
    */
   public function verifyCompletion()
   {
       header('Content-Type: application/json');


       if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
           echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
           return;
       }


       $userId = $_SESSION['user_id'];
       $eventId = (int)($_POST['event_id'] ?? 0);
       $action = trim($_POST['action'] ?? ''); // 'agreed' or 'report'
       $disputeReason = trim($_POST['dispute_reason'] ?? '');


       if (!$eventId || !in_array($action, ['agreed', 'report'])) {
           echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
           return;
       }


       // Get event
       $this->db->query("SELECT * FROM chat_transaction_events WHERE id = :event_id");
       $this->db->bind(':event_id', $eventId);
       $event = $this->db->single();


       if (!$event) {
           echo json_encode(['success' => false, 'message' => 'Event not found.']);
           return;
       }

       if ($event->status === 'completed') {
           echo json_encode(['success' => true, 'message' => 'Transaction already completed.']);
           return;
       }


       // Verify user is the learner
       if ($event->learner_id != $userId) {
           echo json_encode(['success' => false, 'message' => 'Only the learner can verify completion.']);
           return;
       }


       // Must be in 'teacher_completed' status
       if ($event->status !== 'teacher_completed') {
           echo json_encode(['success' => false, 'message' => 'Cannot verify at this time.']);
           return;
       }


       if ($action === 'report') {
           // Handle dispute
           $this->db->query("
               UPDATE chat_transaction_events
               SET status = 'disputed',
                   dispute_flag = TRUE,
                   dispute_reason = :reason,
                   dispute_created_at = NOW()
               WHERE id = :event_id
           ");
           $this->db->bind(':reason', $disputeReason);
           $this->db->bind(':event_id', $eventId);
           $this->db->execute();


           // Notification to teacher
           $this->createNotification($eventId, $event->teacher_id, 'dispute_created',
               'The learner has reported an issue with the transaction.');


           echo json_encode(['success' => true, 'message' => 'Dispute reported. An admin will review.']);
           return;
       }

       try {
           // AGREED - Complete the transaction
           $this->db->beginTransaction();

           // Update event status
           $this->db->query("
               UPDATE chat_transaction_events
               SET status = 'completed',
                   learner_verified_at = NOW()
               WHERE id = :event_id
           ");
           $this->db->bind(':event_id', $eventId);
           $this->db->execute();


           // Transfer BuckX if applicable
           if ($event->payment_type === 'buckx') {
               $this->transferBuckX($eventId, $event->learner_id, $event->teacher_id, $event->amount);
           }


           $skillSettlement = ['applied_hours' => 0, 'remaining_hours' => 0];

           // Activate SkillX debt if applicable
           if ($event->payment_type === 'skillx') {
               $skillSettlement = $this->settleSkillDebtForSession($event);
           }


           $this->db->commit();


           // Notifications
           $teacherMessage = 'Payment has been transferred. Transaction complete!';
           $learnerMessage = 'Transaction completed successfully!';
           $responseMessage = 'Transaction completed successfully!';

           if ($event->payment_type === 'skillx' && $skillSettlement['applied_hours'] > 0) {
               $formattedApplied = number_format((float)$skillSettlement['applied_hours'], 2);
               $teacherMessage = "Transaction complete. {$formattedApplied} skill hour(s) were settled from debt you owed the learner.";
               $learnerMessage = "Transaction completed. {$formattedApplied} skill hour(s) were paid using existing debt owed to you.";
               $responseMessage = $learnerMessage;

               if ($skillSettlement['remaining_hours'] > 0) {
                   $formattedRemaining = number_format((float)$skillSettlement['remaining_hours'], 2);
                   $teacherMessage .= " Remaining debt created for learner: {$formattedRemaining} hour(s).";
                   $learnerMessage .= " Remaining skill debt created: {$formattedRemaining} hour(s).";
                   $responseMessage = $learnerMessage;
               }
           }

           $this->createNotification($eventId, $event->teacher_id, 'payment_transferred', $teacherMessage);
           $this->createNotification($eventId, $event->learner_id, 'payment_transferred', $learnerMessage);


           echo json_encode(['success' => true, 'message' => $responseMessage]);


       } catch (\Throwable $e) {
           if (method_exists($this->db, 'inTransaction') && $this->db->inTransaction()) {
               $this->db->rollBack();
           }
           error_log("Verify completion error: " . $e->getMessage());
           echo json_encode(['success' => false, 'message' => $e->getMessage() ?: 'Failed to complete transaction.']);
       }
   }


   // ============================================
   // 6. GET ACTIVE EVENT FOR CHAT
   // ============================================


   /**
    * Get active transaction event for a chat
    * GET /transaction/getActiveEvent?chat_id=X
    */
   public function getActiveEvent()
   {
       header('Content-Type: application/json');


       $chatId = (int)($_GET['chat_id'] ?? 0);
       $userId = $_SESSION['user_id'];


       if (!$chatId) {
           echo json_encode(['success' => false, 'message' => 'Invalid chat ID.']);
           return;
       }


       // Get active event
       $this->db->query("
           SELECT e.*,
                  u1.username AS teacher_name,
                  u2.username AS learner_name
           FROM chat_transaction_events e
           INNER JOIN users u1 ON e.teacher_id = u1.id
           INNER JOIN users u2 ON e.learner_id = u2.id
           WHERE e.chat_id = :chat_id
           AND e.status IN ('pending_learner', 'pending_teacher', 'active', 'teacher_completed')
           ORDER BY e.created_at DESC
           LIMIT 1
       ");
       $this->db->bind(':chat_id', $chatId);
       $event = $this->db->single();


       if (!$event) {
           echo json_encode(['success' => true, 'has_event' => false]);
           return;
       }


       // Determine user's role in this transaction
       $userRole = ($event->teacher_id == $userId) ? 'teacher' : 'learner';


       echo json_encode([
           'success' => true,
           'has_event' => true,
           'event' => [
               'id' => $event->id,
               'payment_type' => $event->payment_type,
               'amount' => $event->amount,
               'skill_debt_hours' => $event->skill_debt_hours,
               'skill_name' => $event->skill_name,
               'timeframe_hours' => $event->agreed_timeframe_hours,
               'status' => $event->status,
               'teacher_name' => $event->teacher_name,
               'learner_name' => $event->learner_name,
               'expires_at' => $event->expires_at,
               'teacher_completed_at' => $event->teacher_completed_at,
               'user_role' => $userRole
           ]
       ]);
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
           "BuckX released back to learner for event #{$eventId}");
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


       if (!$this->updateWalletBalance($learnerId, $amount, 'subtract')) {
           throw new Exception('Failed to deduct BuckX from learner wallet.');
       }

       if (!$this->updateWalletBalance($teacherId, $amount, 'add')) {
           throw new Exception('Failed to credit BuckX to teacher wallet.');
       }

       $this->logWalletTransaction(
           $learnerId,
           $teacherId,
           $amount,
           "BuckX transferred for completed session #{$eventId}",
           'transfer'
       );


       // Log transaction
       $this->logTransaction($eventId, 'buckx_transfer', $learnerId, $teacherId, $amount, null,
           "BuckX transferred for completed transaction #{$eventId}");
   }


   /**
    * Void SkillX debt (cancel pending debt)
    */
   private function voidSkillDebt($eventId)
   {
       $this->db->query("UPDATE skill_debt SET status = 'voided', voided_at = NOW() WHERE event_id = :event_id");
       $this->db->bind(':event_id', $eventId);
       $this->db->execute();


       // Log transaction
       $this->logTransaction($eventId, 'skillx_void', null, null, null, null,
           "SkillX debt voided for event #{$eventId}");
   }


   /**
    * Activate SkillX debt (learner now owes teacher)
    */
   /**
    * Create a notification for a user
    */
   private function createNotification($eventId, $userId, $type, $message)
   {
       $this->db->query("
           INSERT INTO transaction_notifications (event_id, user_id, type, message, sent)
           VALUES (:event_id, :user_id, :type, :message, FALSE)
       ");
       $this->db->bind(':event_id', $eventId);
       $this->db->bind(':user_id', $userId);
       $this->db->bind(':type', $type);
       $this->db->bind(':message', $message);
       $this->db->execute();


       // Also create in main notifications table
       $titleMap = [
           'offer_received' => 'Transaction Offer',
           'session_started' => 'Session Started',
           'teacher_completed' => 'Session Completed',
           'dispute_created' => 'Issue Reported',
           'payment_transferred' => 'Transaction Complete',
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
    * Log transaction in history
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
}
?>
