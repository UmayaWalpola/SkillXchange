<?php
/**
 * WalletController - Simplified Version (No Filtering)
 * Features:
 * - Transaction logging with sender, receiver, amount, note, timestamp
 * - Real-time notifications for sent/received BuckX
 * - Low balance alerts
 * - Dropdown showing all available users (no filtering for now)
 */

class WalletController extends Controller {

    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Show wallet page with balance and transactions
     */
    public function index() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/auth/signin');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['role'];

        // Ensure wallet exists
        $this->ensureWallet($userId, $userRole);

        // Get wallet data
        $balance = $this->getBalance($userId);
        $transactions = $this->getTransactions($userId);
        $totalSent = $this->getTotalSent($userId);
        $totalReceived = $this->getTotalReceived($userId);
        
        // Get unread notifications count
        $unreadNotifications = $this->getUnreadNotificationsCount($userId);
        
        // Get list of users who can receive BuckX
        $allowedRecipients = $this->getAllowedRecipients($userId, $userRole);
        
        // Check for low balance
        $this->checkLowBalance($userId, $balance);

        // Prepare data for view
        $data = [
            'balance' => number_format($balance, 2),
            'totalSent' => number_format($totalSent, 2),
            'totalReceived' => number_format($totalReceived, 2),
            'sentTransactions' => $transactions['sent'],
            'receivedTransactions' => $transactions['received'],
            'unreadNotifications' => $unreadNotifications,
            'allowedRecipients' => $allowedRecipients,
            'lowBalanceThreshold' => 50.00,
            'userRole' => $userRole
        ];

        // Load appropriate view based on user role
        if ($userRole === 'organization') {
            $this->view('organization/wallet', $data);
        } else {
            $this->view('users/wallet', $data);
        }
    }

    /**
     * Get list of users that current user can send BuckX to
     * SIMPLIFIED VERSION - NO FILTERING
     * Regular users: Can send to all the individuals
     * Organizations: Can send to all the individuals
     */
    private function getAllowedRecipients($userId, $userRole) {
        if ($userRole === 'organization') {
            // Organizations can send to all the individuals 
            $this->db->query("
                SELECT id, username, email, role
                FROM users
                WHERE role = 'individual'
                AND id != :user_id
                ORDER BY username ASC
            ");
            $this->db->bind(':user_id', $userId);
            
        } else {
            // Regular users can send to all the individuals
            $this->db->query("
                SELECT id, username, email, role
                FROM users
                WHERE role = 'individual'
                AND id != :user_id
                ORDER BY username ASC
            ");
            $this->db->bind(':user_id', $userId);
        }
        
        $recipients = $this->db->resultSet();
        
        return $recipients ? $recipients : [];
    }

    //Show transfer confirmation page
     
    public function confirmTransfer() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/auth/signin');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/wallet');
            exit;
        }

        $recipientId = intval($_POST['recipient_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);
        $note = trim($_POST['note'] ?? '');

        // Validate inputs
        $errors = $this->validateTransferInputs($recipientId, $amount);
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            header('Location: ' . URLROOT . '/wallet');
            exit;
        }

        // Get recipient details
        $recipientUser = $this->getUserById($recipientId);
        
        if (!$recipientUser) {
            $_SESSION['error'] = 'User not found';
            header('Location: ' . URLROOT . '/wallet');
            exit;
        }

        // Get sender balance
        $senderBalance = $this->getBalance($_SESSION['user_id']);

        // Prepare confirmation data
        $data = [
            'recipient' => $recipientUser,
            'amount' => $amount,
            'note' => $note,
            'senderBalance' => $senderBalance,
            'remainingBalance' => $senderBalance - $amount,
            'userRole' => $_SESSION['role']
        ];

        // Load appropriate confirmation view based on user role
        if ($_SESSION['role'] === 'organization') {
            $this->view('organization/confirm_transfer', $data);
        } else {
            $this->view('users/confirm_transfer', $data);
        }
    }

    //Process transfer after confirmation (AJAX)
    
    public function processTransfer() {
        // Set JSON header
        header('Content-Type: application/json');

        // Check login
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        // Only POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        $recipientId = intval($_POST['recipient_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);
        $note = trim($_POST['note'] ?? '');

        // Validate
        $errors = $this->validateTransferInputs($recipientId, $amount);
        
        if (!empty($errors)) {
            echo json_encode(['success' => false, 'message' => implode('<br>', $errors)]);
            exit;
        }

        // Send money
        $result = $this->sendMoney($_SESSION['user_id'], $recipientId, $amount, $note);

        // Return response
        echo json_encode($result);
        exit;
    }

    //Get wallet notifications (AJAX)
    
    public function getNotifications() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $notifications = $this->getUserNotifications($_SESSION['user_id']);
        
        echo json_encode([
            'success' => true,
            'notifications' => $notifications
        ]);
        exit;
    }

    //Mark notification as read (AJAX)
    
    public function markNotificationRead() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        $notificationId = intval($_POST['notification_id'] ?? 0);
        
        if ($notificationId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
            exit;
        }

        $result = $this->markAsRead($notificationId, $_SESSION['user_id']);
        
        echo json_encode($result);
        exit;
    }

    //Get current balance
    public function getCurrentBalance() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $balance = $this->getBalance($_SESSION['user_id']);
        
        echo json_encode([
            'success' => true,
            'balance' => number_format($balance, 2),
            'raw_balance' => $balance
        ]);
        exit;
    }
    
    //Ensure wallet exists for user
    //Works with or without database triggers
    
    private function ensureWallet($userId, $userRole) {
        // Check if wallet exists
        $this->db->query("SELECT id, balance FROM wallets WHERE user_id = :user_id");
        $this->db->bind(':user_id', $userId);
        $wallet = $this->db->single();
        
        if (!$wallet) {
            // Wallet doesn't exist - create it
            $initialAmount = ($userRole === 'organization') ? 1000.00 : 250.00;
            
            try {
                // Try to insert with balance (works with or without trigger)
                $this->db->query("INSERT INTO wallets (user_id, balance) VALUES (:user_id, :balance)");
                $this->db->bind(':user_id', $userId);
                $this->db->bind(':balance', $initialAmount);
                $this->db->execute();
                
                // Get the actual balance (in case trigger changed it)
                $newBalance = $this->getBalance($userId);
                
                // Send welcome notification
                $this->createNotification(
                    $userId,
                    null,
                    'system',
                    'Welcome to BuckX! 💰',
                    sprintf(
                        'Your wallet has been created with %s BuckX initial balance. Start exchanging skills!',
                        number_format($newBalance, 2)
                    )
                );
                
            } catch (Exception $e) {
                // If insert fails, log error
                error_log("Wallet creation error for user $userId: " . $e->getMessage());
                
                // Try one more time with just user_id (let trigger handle balance)
                try {
                    $this->db->query("INSERT INTO wallets (user_id) VALUES (:user_id)");
                    $this->db->bind(':user_id', $userId);
                    $this->db->execute();
                } catch (Exception $e2) {
                    error_log("Second wallet creation attempt failed: " . $e2->getMessage());
                }
            }
            
        } else {
            // Wallet exists - verify it has a valid balance
            if ($wallet->balance === null || $wallet->balance < 0) {
                $initialAmount = ($userRole === 'organization') ? 1000.00 : 250.00;
                
                // Only update if balance is invalid
                $this->db->query("UPDATE wallets SET balance = :balance WHERE user_id = :user_id AND (balance IS NULL OR balance < 0)");
                $this->db->bind(':balance', $initialAmount);
                $this->db->bind(':user_id', $userId);
                $this->db->execute();
            }
        }
    }

    //Get user balance
    
    private function getBalance($userId) {
        $this->db->query("SELECT balance FROM wallets WHERE user_id = :user_id");
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        return $result ? floatval($result->balance) : 0;
    }

    //Get total sent amount
    
    private function getTotalSent($userId) {
        $this->db->query("SELECT COALESCE(SUM(amount), 0) as total 
                         FROM wallet_transactions 
                         WHERE sender_id = :user_id AND status = 'completed'");
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        return floatval($result->total);
    }

    //Get total received amount
    
    private function getTotalReceived($userId) {
        $this->db->query("SELECT COALESCE(SUM(amount), 0) as total 
                         FROM wallet_transactions 
                         WHERE receiver_id = :user_id AND status = 'completed'");
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        return floatval($result->total);
    }

    //Get user transactions (sent and received)
    
    private function getTransactions($userId) {
        // Sent transactions
        $this->db->query("SELECT wt.*, u.username as receiver, u.role as receiver_role,
                         DATE_FORMAT(wt.created_at, '%Y-%m-%d %H:%i') as timestamp
                         FROM wallet_transactions wt
                         JOIN users u ON wt.receiver_id = u.id
                         WHERE wt.sender_id = :user_id
                         ORDER BY wt.created_at DESC
                         LIMIT 50");
        $this->db->bind(':user_id', $userId);
        $sent = $this->db->resultSet();
        
        // Received transactions
        $this->db->query("SELECT wt.*, u.username as sender, u.role as sender_role,
                         DATE_FORMAT(wt.created_at, '%Y-%m-%d %H:%i') as timestamp
                         FROM wallet_transactions wt
                         JOIN users u ON wt.sender_id = u.id
                         WHERE wt.receiver_id = :user_id
                         ORDER BY wt.created_at DESC
                         LIMIT 50");
        $this->db->bind(':user_id', $userId);
        $received = $this->db->resultSet();
        
        return ['sent' => $sent, 'received' => $received];
    }

    //Get user by ID
    private function getUserById($userId) {
        $this->db->query("SELECT id, username, email, role FROM users WHERE id = :id");
        $this->db->bind(':id', $userId);
        return $this->db->single();
    }

    //Validate transfer inputs
    
    private function validateTransferInputs($recipientId, $amount) {
        $errors = [];
        
        if ($recipientId <= 0) {
            $errors[] = 'Please select a valid recipient';
        }
        
        if ($amount <= 0) {
            $errors[] = 'Amount must be greater than 0';
        }
        
        if ($amount > 1000) {
            $errors[] = 'Amount cannot exceed 1000 BuckX per transaction';
        }
        
        return $errors;
    }

    //Send money from one user to another
    private function sendMoney($senderId, $receiverId, $amount, $note) {
        try {
            // Get receiver details
            $receiver = $this->getUserById($receiverId);
            
            if (!$receiver) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            // Can't send to yourself
            if ($senderId == $receiverId) {
                return ['success' => false, 'message' => 'Cannot send BuckX to yourself'];
            }
            
            // Check balance
            $senderBalance = $this->getBalance($senderId);
            if ($senderBalance < $amount) {
                return ['success' => false, 'message' => 'Insufficient balance'];
            }
            
            // Start transaction
            $this->db->query("START TRANSACTION");
            
            // Deduct from sender
            $this->db->query("UPDATE wallets SET balance = balance - :amount WHERE user_id = :user_id");
            $this->db->bind(':amount', $amount);
            $this->db->bind(':user_id', $senderId);
            
            if (!$this->db->execute()) {
                throw new Exception("Failed to deduct from sender");
            }
            
            // Add to receiver
            $this->db->query("UPDATE wallets SET balance = balance + :amount WHERE user_id = :user_id");
            $this->db->bind(':amount', $amount);
            $this->db->bind(':user_id', $receiverId);
            
            if (!$this->db->execute()) {
                throw new Exception("Failed to add to receiver");
            }
            
            // LOG TRANSACTION: sender, receiver, amount, note (reason), timestamp (auto-created)
            $this->db->query("INSERT INTO wallet_transactions 
                             (sender_id, receiver_id, amount, note, transaction_type, status, created_at) 
                             VALUES (:sender, :receiver, :amount, :note, 'transfer', 'completed', NOW())");
            $this->db->bind(':sender', $senderId);
            $this->db->bind(':receiver', $receiverId);
            $this->db->bind(':amount', $amount);
            $this->db->bind(':note', $note);
            
            if (!$this->db->execute()) {
                throw new Exception("Failed to log transaction");
            }
            
            $transactionId = $this->db->lastInsertId();
            
            // Get sender username
            $this->db->query("SELECT username FROM users WHERE id = :user_id");
            $this->db->bind(':user_id', $senderId);
            $senderUser = $this->db->single();
            $senderUsername = $senderUser->username;
            
            // Create notification for sender
            $this->createNotification(
                $senderId,
                $transactionId,
                'sent',
                'BuckX Sent Successfully ✅',
                "You sent {$amount} BuckX to {$receiver->username}" . ($note ? " - Reason: {$note}" : "")
            );
            
            // Create notification for receiver
            $this->createNotification(
                $receiverId,
                $transactionId,
                'received',
                'BuckX Received! 💰',
                "You received {$amount} BuckX from {$senderUsername}" . ($note ? " - Reason: {$note}" : "")
            );
            
            // Commit transaction
            $this->db->query("COMMIT");
            
            // Get new balance
            $newBalance = $this->getBalance($senderId);
            
            // Check if sender now has low balance
            $this->checkLowBalance($senderId, $newBalance);
            
            return [
                'success' => true,
                'message' => 'Transfer successful! '. number_format($amount, 2) . ' BuckX sent to ' . $receiver->username,
                'new_balance' => number_format($newBalance, 2),
                'raw_balance' => $newBalance
            ];
            
        } catch (Exception $e) {
            // Rollback on error
            $this->db->query("ROLLBACK");
            error_log("Transfer Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Transfer failed. Please try again.'];
        }
    }

    //Create notification for user

    private function createNotification($userId, $transactionId, $type, $title, $message) {
        $this->db->query("INSERT INTO wallet_notifications 
                         (user_id, transaction_id, type, title, message, created_at) 
                         VALUES (:user_id, :transaction_id, :type, :title, :message, NOW())");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':transaction_id', $transactionId);
        $this->db->bind(':type', $type);
        $this->db->bind(':title', $title);
        $this->db->bind(':message', $message);
        $this->db->execute();
    }

    //Check for low balance and send notification if needed
    
    private function checkLowBalance($userId, $balance) {
        $threshold = 20.00;
        
        if ($balance <= $threshold && $balance > 0) {
            // Check if we already sent a low balance notification recently (within 24 hours)
            $this->db->query("SELECT id FROM wallet_notifications 
                             WHERE user_id = :user_id 
                             AND type = 'low_balance' 
                             AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                             LIMIT 1");
            $this->db->bind(':user_id', $userId);
            
            if (!$this->db->single()) {
                // No recent notification, send one
                $this->createNotification(
                    $userId,
                    null,
                    'low_balance',
                    'Low Balance Alert ⚠️',
                    "Your BuckX balance is running low: " . number_format($balance, 2) . " BuckX remaining"
                );
            }
        }
    }

    //Get user notifications
    
    private function getUserNotifications($userId, $limit = 20) {
        $this->db->query("SELECT * FROM wallet_notifications 
                         WHERE user_id = :user_id 
                         ORDER BY created_at DESC 
                         LIMIT :limit");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    //Get unread notifications count
     
    private function getUnreadNotificationsCount($userId) {
        $this->db->query("SELECT COUNT(*) as count FROM wallet_notifications 
                         WHERE user_id = :user_id AND is_read = 0");
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        return $result ? intval($result->count) : 0;
    }

    //Mark notification as read
    
    private function markAsRead($notificationId, $userId) {
        $this->db->query("UPDATE wallet_notifications 
                         SET is_read = 1 
                         WHERE id = :id AND user_id = :user_id");
        $this->db->bind(':id', $notificationId);
        $this->db->bind(':user_id', $userId);
        
        if ($this->db->execute()) {
            return ['success' => true, 'message' => 'Notification marked as read'];
        } else {
            return ['success' => false, 'message' => 'Failed to update notification'];
        }
    }
    /**
 * Show purchase BuckX page (Organizations only)
 */
public function purchaseBuckx() {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . URLROOT . '/auth/signin');
        exit;
    }

    // Only organizations can purchase
    if ($_SESSION['role'] !== 'organization') {
        $_SESSION['error'] = 'Only organizations can purchase BuckX';
        header('Location: ' . URLROOT . '/wallet');
        exit;
    }

    $data = [
        'userRole' => $_SESSION['role']
    ];

    $this->view('organization/purchase_buckx', $data);
}

/**
 * Initiate purchase (creates pending transaction)
 */
public function initiatePurchase() {
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organization') {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }

    $buckxAmount = intval($_POST['buckx_amount'] ?? 0);

    // Validate
    if ($buckxAmount < 100) {
        $_SESSION['error'] = 'Minimum purchase is 100 BuckX';
        header('Location: ' . URLROOT . '/wallet/purchaseBuckx');
        exit;
    }

    $priceInLKR = $buckxAmount * 100;
    $orgId = $_SESSION['user_id'];

    try {
        // Create pending purchase record
        $this->db->query("INSERT INTO buckx_purchases 
                         (org_id, buckx_amount, price_lkr, status, created_at) 
                         VALUES (:org_id, :amount, :price, 'pending', NOW())");
        $this->db->bind(':org_id', $orgId);
        $this->db->bind(':amount', $buckxAmount);
        $this->db->bind(':price', $priceInLKR);
        $this->db->execute();

        $purchaseId = $this->db->lastInsertId();

        // TODO: Redirect to payment gateway (Stripe/Razorpay)
        // For now, simulate success
        $_SESSION['purchase_id'] = $purchaseId;
        header('Location: ' . URLROOT . '/wallet/paymentGateway/' . $purchaseId);
        exit;

    } catch (Exception $e) {
        error_log("Purchase Error: " . $e->getMessage());
        $_SESSION['error'] = 'Failed to initiate purchase';
        header('Location: ' . URLROOT . '/wallet/purchaseBuckx');
        exit;
    }
}

/**
 * Payment gateway page (placeholder for now)
 */
public function paymentGateway($purchaseId = null) {
    if (!$purchaseId || !isset($_SESSION['user_id'])) {
        header('Location: ' . URLROOT . '/wallet');
        exit;
    }

    // Get purchase details
    $this->db->query("SELECT * FROM buckx_purchases WHERE id = :id AND org_id = :org_id");
    $this->db->bind(':id', $purchaseId);
    $this->db->bind(':org_id', $_SESSION['user_id']);
    $purchase = $this->db->single();

    if (!$purchase) {
        $_SESSION['error'] = 'Purchase not found';
        header('Location: ' . URLROOT . '/wallet');
        exit;
    }

    $data = [
        'purchase' => $purchase,
        'userRole' => $_SESSION['role']
    ];

    $this->view('organization/payment_gateway', $data);
}
}