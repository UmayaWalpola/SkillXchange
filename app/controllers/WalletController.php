<?php

class WalletController extends Controller {

    private $walletModel;
    private $notificationModel;
    
    public function __construct() {
        $this->walletModel = $this->model('Wallet');
        $this->notificationModel = $this->model('WalletNotification');
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
        $this->walletModel->ensureWalletExists($userId, $userRole);

        // Get wallet data using model
        $balance = $this->walletModel->getBalance($userId);
        $transactions = $this->walletModel->getTransactions($userId);
        $totalSent = $this->walletModel->getTotalSent($userId);
        $totalReceived = $this->walletModel->getTotalReceived($userId);
        
        // Get notifications
        $unreadNotifications = $this->notificationModel->getUnreadCount($userId);
        
        // Get allowed recipients
        $allowedRecipients = $this->walletModel->getAllowedRecipients($userId, $userRole);
        
        // Check for low balance
        $this->notificationModel->checkLowBalance($userId, $balance);

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
     * Show transfer confirmation page
     */
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

        // Get recipient details using User model (if it exists)
        // For now, we'll get it from wallet model
        $userModel = $this->model('User');
        $recipientUser = $userModel->getUserById($recipientId);
        
        if (!$recipientUser) {
            $_SESSION['error'] = 'User not found';
            header('Location: ' . URLROOT . '/wallet');
            exit;
        }

        // Get sender balance
        $senderBalance = $this->walletModel->getBalance($_SESSION['user_id']);

        // Prepare confirmation data
        $data = [
            'recipient' => $recipientUser,
            'amount' => $amount,
            'note' => $note,
            'senderBalance' => $senderBalance,
            'remainingBalance' => $senderBalance - $amount,
            'userRole' => $_SESSION['role']
        ];

        // Load appropriate confirmation view
        if ($_SESSION['role'] === 'organization') {
            $this->view('organization/confirm_transfer', $data);
        } else {
            $this->view('users/confirm_transfer', $data);
        }
    }

    /**
     * Process transfer after confirmation (AJAX)
     */
    public function processTransfer() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

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

        // Transfer money using model
        $result = $this->walletModel->transferMoney($_SESSION['user_id'], $recipientId, $amount, $note);

        if ($result['success']) {
            // Get sender and receiver details
            $userModel = $this->model('User');
            $sender = $userModel->getUserById($_SESSION['user_id']);
            $receiver = $userModel->getUserById($recipientId);

            // Create notifications
            $this->notificationModel->create(
                $_SESSION['user_id'],
                $result['transaction_id'],
                'sent',
                'BuckX Sent Successfully ✅',
                "You sent {$amount} BuckX to {$receiver->username}" . ($note ? " - Reason: {$note}" : "")
            );

            $this->notificationModel->create(
                $recipientId,
                $result['transaction_id'],
                'received',
                'BuckX Received! 💰',
                "You received {$amount} BuckX from {$sender->username}" . ($note ? " - Reason: {$note}" : "")
            );

            // Check low balance
            $this->notificationModel->checkLowBalance($_SESSION['user_id'], $result['new_balance']);
        }

        echo json_encode($result);
        exit;
    }

    /**
     * Show purchase BuckX page (Organizations only)
     */
    public function purchaseBuckx() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/auth/signin');
            exit;
        }

        if ($_SESSION['role'] !== 'organization') {
            $_SESSION['error'] = 'Only organizations can purchase BuckX';
            header('Location: ' . URLROOT . '/wallet');
            exit;
        }

        $data = ['userRole' => $_SESSION['role']];
        $this->view('organization/purchase_buckx', $data);
    }

    /**
     * Process BuckX purchase
     */
    public function processPurchase() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organization') {
            $_SESSION['error'] = 'Access denied';
            header('Location: ' . URLROOT . '/wallet');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/wallet/purchaseBuckx');
            exit;
        }

        $buckxAmount = intval($_POST['buckx_amount'] ?? 0);
        $cardHolder = trim($_POST['card_holder'] ?? '');
        $cardNumber = str_replace(' ', '', trim($_POST['card_number'] ?? ''));
        $expiryDate = trim($_POST['expiry_date'] ?? '');
        $cvv = trim($_POST['cvv'] ?? '');

        // Validate
        $errors = $this->validatePurchaseInputs($buckxAmount, $cardHolder, $cardNumber, $expiryDate, $cvv);

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            header('Location: ' . URLROOT . '/wallet/purchaseBuckx');
            exit;
        }

        $priceLKR = $buckxAmount * 100;
        $orgId = $_SESSION['user_id'];

        // Create purchase using model
        $result = $this->walletModel->createPurchase($orgId, $buckxAmount, $priceLKR, 'card');

        if ($result['success']) {
            // Create notification
            $this->notificationModel->create(
                $orgId,
                $result['purchase_id'],
                'purchase',
                'BuckX Purchase Successful! 🎉',
                "You successfully purchased {$buckxAmount} BuckX for LKR " . number_format($priceLKR, 2)
            );

            $_SESSION['success'] = $result['message'] . " Your wallet has been credited.";
        } else {
            $_SESSION['error'] = $result['message'];
        }

        header('Location: ' . URLROOT . '/wallet');
        exit;
    }

    /**
     * Get wallet notifications (AJAX)
     */
    public function getNotifications() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $notifications = $this->notificationModel->getUserNotifications($_SESSION['user_id']);
        
        echo json_encode([
            'success' => true,
            'notifications' => $notifications
        ]);
        exit;
    }

    /**
     * Mark notification as read (AJAX)
     */
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

        $success = $this->notificationModel->markAsRead($notificationId, $_SESSION['user_id']);
        
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Notification marked as read' : 'Failed to update notification'
        ]);
        exit;
    }

    /**
     * Get current balance (AJAX)
     */
    public function getCurrentBalance() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $balance = $this->walletModel->getBalance($_SESSION['user_id']);
        
        echo json_encode([
            'success' => true,
            'balance' => number_format($balance, 2),
            'raw_balance' => $balance
        ]);
        exit;
    }

    // ============================================
    // VALIDATION HELPERS
    // ============================================

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

    private function validatePurchaseInputs($amount, $cardHolder, $cardNumber, $expiryDate, $cvv) {
        $errors = [];
        
        if ($amount < 100) {
            $errors[] = 'Minimum purchase is 100 BuckX';
        }
        
        if (empty($cardHolder)) {
            $errors[] = 'Cardholder name is required';
        }
        
        if (strlen($cardNumber) < 13 || strlen($cardNumber) > 19) {
            $errors[] = 'Invalid card number';
        }
        
        if (empty($expiryDate) || !preg_match('/^\d{2}\/\d{2}$/', $expiryDate)) {
            $errors[] = 'Invalid expiry date format';
        }
        
        if (strlen($cvv) !== 3 && strlen($cvv) !== 4) {
            $errors[] = 'Invalid CVV';
        }

        return $errors;
    }
}