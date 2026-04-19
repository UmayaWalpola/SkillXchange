<?php

class WalletController extends Controller {

    private $walletModel;
    private $notificationModel;
    
    public function __construct() {
        $this->walletModel = $this->model('Wallet');
        $this->notificationModel = $this->model('WalletNotification');
    }

    //Show wallet page with balance and transactions
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/auth/signin');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['role'];

        $this->walletModel->ensureWalletExists($userId, $userRole);

        $balance = $this->walletModel->getBalance($userId);
        $transactions = $this->walletModel->getTransactions($userId);
        $totalSent = $this->walletModel->getTotalSent($userId);
        $totalReceived = $this->walletModel->getTotalReceived($userId);
        $debts = $this->walletModel->getDebts($userId);
        $unreadNotifications = $this->notificationModel->getUnreadCount($userId);
        
        $this->notificationModel->checkLowBalance($userId, $balance);

        $data = [
            'balance' => number_format($balance, 2),
            'totalSent' => number_format($totalSent, 2),
            'totalReceived' => number_format($totalReceived, 2),
            'sentTransactions' => $transactions['sent'],
            'receivedTransactions' => $transactions['received'],
            'unreadNotifications' => $unreadNotifications,
            'lowBalanceThreshold' => 50.00,
            'userRole' => $userRole,
            'debtsOwed'    => $debts['owed'],       // I owe these
            'debtsOwedToMe' => $debts['owed_to_me'] // owed to me
        ];

        // Add pending BuckX allocations for organizations
        if ($userRole === 'organization') {
            $pendingAllocations = $this->walletModel->getPendingAllocations($userId);
            $totalPending = $this->walletModel->getTotalPendingAllocation($userId);
            $availableBalance = $this->walletModel->getAvailableBalance($userId, $userRole);
            
            $data['pendingAllocations'] = $pendingAllocations;
            $data['totalPendingAllocation'] = number_format($totalPending, 2);
            $data['availableBalance'] = number_format($availableBalance, 2);
        }

        if ($userRole === 'organization') {
            $this->view('organization/wallet', $data);
        } else {
            $this->view('users/wallet', $data);
        }
    }

    

    //Show purchase BuckX page - STRIPE VERSION (Organizations only)
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

        // Get current balance
        $currentBalance = $this->walletModel->getBalance($_SESSION['user_id']);
        
        // Get packages using Wallet model
        $packages = $this->walletModel->getActivePackages();

        if (!$packages) {
            $packages = [];
        }

        $data = [
            'userRole' => $_SESSION['role'],
            'currentBalance' => number_format($currentBalance, 2),
            'packages' => $packages
        ];

        $this->view('organization/purchase_buckx', $data);
    }

    //Create Stripe checkout session 
    public function createCheckoutSession() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organization') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        $packageId = intval($_POST['package_id'] ?? 0);
        $userId = $_SESSION['user_id'];

        // Get package - works with your existing Wallet model
        $packages = $this->walletModel->getActivePackages();
        $package = null;

        foreach ($packages as $pkg) {
            if ($pkg['package_id'] == $packageId) {
                $package = (object) $pkg; // Convert array to object for consistency
                break;
            }
        }

        if (!$package) {
            echo json_encode(['success' => false, 'message' => 'Invalid package']);
            exit;
        }

        // Load BuckxPurchase model using the model() method
        $purchaseModel = $this->model('BuckxPurchase');

        // Create purchase record
        $purchaseId = $purchaseModel->createPurchase(
            $userId,
            $package->package_id,
            $package->buckx_amount,
            $package->price_lkr
        );

        if (!$purchaseId) {
            echo json_encode(['success' => false, 'message' => 'Failed to create purchase record']);
            exit;
        }

        $purchaseModel->logPaymentEvent($userId, $purchaseId, 'purchase_initiated');

        // Load Stripe service
        require_once __DIR__ . '/../services/StripePaymentService.php';
        $stripeService = new StripePaymentService();
        
        $successUrl = URLROOT . '/wallet/handlePaymentSuccess?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = URLROOT . '/wallet/handlePaymentCancel?purchase_id=' . $purchaseId;
        
        $metadata = [
            'purchase_id' => $purchaseId,
            'user_id' => $userId,
            'package_name' => $package->package_name,
            'buckx_amount' => $package->buckx_amount,
            'description' => "Purchase {$package->buckx_amount} BuckX"
        ];

        $result = $stripeService->createCheckoutSession(
            $package->price_lkr,
            'LKR',
            $successUrl,
            $cancelUrl,
            $metadata
        );

        if ($result['success']) {
            $purchaseModel->updateStripeSession($purchaseId, $result['session_id']);
            
            $purchaseModel->logPaymentEvent($userId, $purchaseId, 'checkout_session_created', [
                'session_id' => $result['session_id']
            ]);

            echo json_encode([
                'success' => true,
                'checkout_url' => $result['checkout_url'],
                'session_id' => $result['session_id']
            ]);
        } else {
            $purchaseModel->failPurchase($purchaseId);
            
            $purchaseModel->logPaymentEvent($userId, $purchaseId, 'checkout_session_failed', [
                'error' => $result['message']
            ]);

            echo json_encode([
                'success' => false,
                'message' => $result['message']
            ]);
        }

        exit;
    }

    //Handle successful payment (callback from Stripe)
    public function handlePaymentSuccess() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/auth/signin');
            exit;
        }

        $sessionId = $_GET['session_id'] ?? null;

        if (!$sessionId) {
            $_SESSION['error'] = 'Invalid payment session';
            header('Location: ' . URLROOT . '/wallet/purchaseBuckx');
            exit;
        }

        require_once __DIR__ . '/../services/StripePaymentService.php';
        $stripeService = new StripePaymentService();
        
        $result = $stripeService->retrieveSession($sessionId);

        if (!$result['success'] || $result['session']['payment_status'] !== 'paid') {
            $_SESSION['error'] = 'Payment verification failed. Please contact support.';
            header('Location: ' . URLROOT . '/wallet/purchaseBuckx');
            exit;
        }

        $session = $result['session'];
        
        // Load BuckxPurchase model using the model() method
        $purchaseModel = $this->model('BuckxPurchase');
        $purchase = $purchaseModel->getPurchaseBySessionId($sessionId);

        if (!$purchase) {
            $_SESSION['error'] = 'Purchase record not found';
            header('Location: ' . URLROOT . '/wallet/purchaseBuckx');
            exit;
        }

        if ($purchase['org_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Unauthorized access';
            header('Location: ' . URLROOT . '/wallet/purchaseBuckx');
            exit;
        }

        // Already completed?
        if ($purchase['status'] === 'completed') {
            $_SESSION['success'] = 'Payment already processed. Check your wallet.';
            header('Location: ' . URLROOT . '/wallet');
            exit;
        }

        // Complete the purchase
        $paymentIntentId = $session['payment_intent'] ?? $sessionId;
        $purchaseModel->completePurchase($purchase['id'], $paymentIntentId);

        // Add BuckX to wallet
        $purchaseModel->addBuckxToWallet($purchase['org_id'], $purchase['buckx_amount']);

        $purchaseModel->logPaymentEvent($purchase['org_id'], $purchase['id'], 'payment_completed', [
            'payment_intent' => $paymentIntentId,
            'amount_paid' => $purchase['price_lkr']
        ]);

        // Success message
        $_SESSION['payment_success'] = [
            'buckx_amount' => $purchase['buckx_amount'],
            'amount_paid' => $purchase['price_lkr'],
            'transaction_id' => $paymentIntentId
        ];

        header('Location: ' . URLROOT . '/wallet/purchaseBuckx');
        exit;
    }

    //Handle cancelled payment
    public function handlePaymentCancel() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/auth/signin');
            exit;
        }

        $purchaseId = $_GET['purchase_id'] ?? null;

        if ($purchaseId) {
            // Load BuckxPurchase model using the model() method
            $purchaseModel = $this->model('BuckxPurchase');
            $purchaseModel->failPurchase($purchaseId);
            
            $purchaseModel->logPaymentEvent($_SESSION['user_id'], $purchaseId, 'payment_cancelled');
        }

        $_SESSION['error'] = 'Payment cancelled. No charges were made.';
        header('Location: ' . URLROOT . '/wallet/purchaseBuckx');
        exit;
    }

    //Get current balance 
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

    // Transfer validation removed.
}