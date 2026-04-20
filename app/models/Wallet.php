<?php

class Wallet {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Ensure wallet exists for user
    public function ensureWalletExists($userId, $userRole) {
        $this->db->query("SELECT id, balance FROM wallets WHERE user_id = :user_id");
        $this->db->bind(':user_id', $userId);
        $wallet = $this->db->single();

        if (!$wallet) {
            $initialAmount = ($userRole === 'organization') ? 1000.00 : 250.00;

            try {
                $this->db->query("INSERT INTO wallets (user_id, balance) VALUES (:user_id, :balance)");
                $this->db->bind(':user_id', $userId);
                $this->db->bind(':balance', $initialAmount);
                $this->db->execute();

                return $initialAmount;
            } catch (Exception $e) {
                error_log("Wallet creation error for user $userId: " . $e->getMessage());
                return 0;
            }
        }

        return floatval($wallet->balance);
    }

    // Get user balance
    public function getBalance($userId) {
        $this->db->query("SELECT balance FROM wallets WHERE user_id = :user_id");
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        return $result ? floatval($result->balance) : 0;
    }

    // Get total sent amount
    public function getTotalSent($userId) {
        $this->db->query("
            SELECT COALESCE(SUM(amount), 0) as total 
            FROM wallet_transactions 
            WHERE sender_id = :user_id AND status = 'completed'
        ");
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        return floatval($result->total);
    }

    // Get total received amount
    public function getTotalReceived($userId) {
        $this->db->query("
            SELECT COALESCE(SUM(amount), 0) as total 
            FROM wallet_transactions 
            WHERE receiver_id = :user_id AND status = 'completed'
        ");
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        return floatval($result->total);
    }

    // Get user transactions (sent and received)
    public function getTransactions($userId) {
        // Sent transactions
        $this->db->query("
            SELECT wt.*, 
                   u.username as receiver, 
                   u.role as receiver_role,
                   DATE_FORMAT(wt.created_at, '%Y-%m-%d %H:%i') as timestamp
            FROM wallet_transactions wt
            INNER JOIN users u ON wt.receiver_id = u.id
            WHERE wt.sender_id = :user_id
            ORDER BY wt.created_at DESC
            LIMIT 50
        ");
        $this->db->bind(':user_id', $userId);
        $sent = $this->db->resultSet();


        // Received transactions
        $this->db->query("
            SELECT wt.*, 
                CASE 
                    WHEN wt.transaction_type = 'reward' THEN wt.note
                    ELSE u.username 
                END as sender, 
                u.role as sender_role,
                DATE_FORMAT(wt.created_at, '%Y-%m-%d %H:%i') as timestamp
            FROM wallet_transactions wt
            INNER JOIN users u ON wt.sender_id = u.id
            WHERE wt.receiver_id = :user_id
            ORDER BY wt.created_at DESC
            LIMIT 50
        ");
        $this->db->bind(':user_id', $userId);
        $received = $this->db->resultSet();

        return ['sent' => $sent, 'received' => $received];
    }

    // Create BuckX purchase record
    public function createPurchase($orgId, $buckxAmount, $priceLKR, $paymentMethod = 'card') {
        try {
            $this->db->query("START TRANSACTION");

            // Create purchase record
            $this->db->query("
                INSERT INTO buckx_purchases 
                (org_id, buckx_amount, price_lkr, payment_method, status, created_at, completed_at) 
                VALUES (:org_id, :amount, :price, :method, 'completed', NOW(), NOW())
            ");
            $this->db->bind(':org_id', $orgId);
            $this->db->bind(':amount', $buckxAmount);
            $this->db->bind(':price', $priceLKR);
            $this->db->bind(':method', $paymentMethod);
            $this->db->execute();

            $purchaseId = $this->db->lastInsertId();

            // Credit BuckX to wallet
            if (!$this->updateBalance($orgId, $buckxAmount, 'add')) {
                throw new Exception("Failed to credit wallet");
            }

            // Create transaction log
            $this->db->query("
                INSERT INTO wallet_transactions 
                (receiver_id, amount, note, transaction_type, status, created_at) 
                VALUES (:receiver_id, :amount, :note, 'purchase', 'completed', NOW())
            ");
            $this->db->bind(':receiver_id', $orgId);
            $this->db->bind(':amount', $buckxAmount);
            $this->db->bind(':note', "Purchased {$buckxAmount} BuckX for LKR " . number_format($priceLKR, 2));
            $this->db->execute();

            $this->db->query("COMMIT");

            return [
                'success' => true,
                'purchase_id' => $purchaseId,
                'message' => "Successfully purchased {$buckxAmount} BuckX!"
            ];

        } catch (Exception $e) {
            $this->db->query("ROLLBACK");
            error_log("Purchase Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Purchase failed. Please try again.'];
        }
    }

    // Get purchase by ID
    public function getPurchaseById($purchaseId, $orgId) {
        $this->db->query("SELECT * FROM buckx_purchases WHERE id = :id AND org_id = :org_id");
        $this->db->bind(':id', $purchaseId);
        $this->db->bind(':org_id', $orgId);
        return $this->db->single();
    }

    // Get all active BuckX packages
    public function getActivePackages() {
        $this->db->query("SELECT * FROM buckx_packages WHERE is_active = TRUE ORDER BY buckx_amount ASC");
        $results = $this->db->resultSet();

        // Convert objects to arrays for the view
        $packages = [];
        if ($results) {
            foreach ($results as $package) {
                $packages[] = (array) $package;
            }
        }

        return $packages;
    }

    // PRIVATE HELPER METHODS
    // Update balance (private helper)

    private function updateBalance($userId, $amount, $operation = 'add') {
        if ($operation === 'add') {
            $this->db->query("UPDATE wallets SET balance = balance + :amount WHERE user_id = :user_id");
        } else {
            $this->db->query("UPDATE wallets SET balance = balance - :amount WHERE user_id = :user_id");
        }

        $this->db->bind(':amount', abs($amount));
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }

    /**
     * Credit a quiz reward to a user's wallet and log it as a reward transaction.
     * - Increases wallet balance
     * - Inserts into wallet_transactions with transaction_type = 'reward'
     */
    public function creditQuizReward($userId, $amount, $quizId = null, $quizTitle = null) {
        $amount = (float)$amount;
        if ($amount <= 0) return false;

        // ensure wallet exists (assume individual by default)
        $this->ensureWalletExists($userId, 'individual');

        // sender_id must reference an existing user because of FK constraints
        $systemSenderId = defined('SYSTEM_REWARD_SENDER_ID') ? (int)SYSTEM_REWARD_SENDER_ID : 1;

        $noteParts = ['Quiz Reward'];
        if (!empty($quizTitle)) $noteParts[] = $quizTitle;
        $note = implode(' ', $noteParts);

        try {
            $this->db->query('START TRANSACTION');

            if (!$this->updateBalance($userId, $amount, 'add')) {
                throw new Exception('Failed to update balance');
            }

            $this->db->query(
                "INSERT INTO wallet_transactions (sender_id, receiver_id, amount, note, transaction_type, status, created_at)
                 VALUES (:sid, :rid, :amt, :note, 'reward', 'completed', NOW())"
            );
            $this->db->bind(':sid', $systemSenderId);
            $this->db->bind(':rid', $userId);
            $this->db->bind(':amt', abs($amount));
            $this->db->bind(':note', $note);

            if (!$this->db->execute()) {
                throw new Exception('Failed to insert wallet transaction');
            }

            $this->db->query('COMMIT');
            return true;
        } catch (Exception $e) {
            $this->db->query('ROLLBACK');
            error_log('creditQuizReward error: ' . $e->getMessage());
            return false;
        }
    }

    public function getDebts($userId) {
        // Debts I OWE
        $this->db->query("
            SELECT sd.*, u.username AS creditor_name
            FROM skill_debt sd
            JOIN users u ON u.id = sd.creditor_id
            WHERE sd.debtor_id = :uid
            AND sd.status IN ('pending', 'active')
            ORDER BY sd.created_at DESC
        ");
        $this->db->bind(':uid', $userId);
        $owed = $this->db->resultSet();

        // Debts OWED TO ME
        $this->db->query("
            SELECT sd.*, u.username AS debtor_name
            FROM skill_debt sd
            JOIN users u ON u.id = sd.debtor_id
            WHERE sd.creditor_id = :uid
            AND sd.status IN ('pending', 'active')
            ORDER BY sd.created_at DESC
        ");
        $this->db->bind(':uid', $userId);
        $owedToMe = $this->db->resultSet();

        return [
            'owed'       => $owed    ? array_map(fn($r) => (array)$r, $owed)      : [],
            'owed_to_me' => $owedToMe ? array_map(fn($r) => (array)$r, $owedToMe) : []
        ];
    }

    public function getCounterpartyDebtSummary($creditorId, $debtorId) {
        $this->db->query("
            SELECT
                COALESCE(SUM(hours_owed), 0) AS total_hours,
                COUNT(*) AS debt_count
            FROM skill_debt
            WHERE creditor_id = :creditor_id
              AND debtor_id = :debtor_id
              AND status = 'active'
        ");
        $this->db->bind(':creditor_id', $creditorId);
        $this->db->bind(':debtor_id', $debtorId);
        $row = $this->db->single();

        return [
            'total_hours' => (float)($row->total_hours ?? 0),
            'debt_count' => (int)($row->debt_count ?? 0),
        ];
    }

    public function getRecentDebtSettlements($userId, $limit = 10) {
        try {
            $this->db->query("
                SELECT
                    s.*,
                    d.username AS debtor_name,
                    c.username AS creditor_name
                FROM skill_debt_settlements s
                INNER JOIN users d ON d.id = s.debtor_id
                INNER JOIN users c ON c.id = s.creditor_id
                WHERE s.debtor_id = :user_id OR s.creditor_id = :user_id
                ORDER BY s.created_at DESC, s.id DESC
                LIMIT :limit
            ");
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':limit', (int)$limit);
            return $this->db->resultSet() ?: [];
        } catch (Exception $e) {
            error_log('Debt settlements unavailable: ' . $e->getMessage());
            return [];
        }
    }

    /* ============================================================
       BUCKX TASK ALLOCATION & REWARD TRANSFER
    ============================================================ */

    /**
     * Mark pending allocations older than 1 year as expired
     */
    public function expireOldAllocations() {
        $this->db->query("
            UPDATE project_tasks 
            SET buckx_allocated = 0, buckx_distributed = 2, buckx_distributed_at = NOW()
            WHERE buckx_allocated > 0 
            AND buckx_distributed = 0 
            AND created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR)
            AND status != 'done'
        ");
        return $this->db->execute();
    }

    /**
     * Get pending Buckx allocations for an organization (excluding expired ones)
     */
    public function getPendingAllocations($organizationId) {
        // First expire old allocations
        $this->expireOldAllocations();
        
        $this->db->query("
            SELECT pt.id, pt.title, pt.assigned_to, pt.buckx_allocated, 
                   u.username as assigned_user, u.profile_picture,
                   p.name as project_name, p.id as project_id,
                   pt.created_at
            FROM project_tasks pt
            INNER JOIN projects p ON pt.project_id = p.id
            LEFT JOIN users u ON pt.assigned_to = u.id
            WHERE p.organization_id = :org_id 
            AND pt.buckx_allocated > 0 
            AND pt.buckx_distributed = 0
            AND pt.status != 'done'
            AND pt.created_at > DATE_SUB(NOW(), INTERVAL 1 YEAR)
            ORDER BY pt.created_at DESC
        ");
        $this->db->bind(':org_id', $organizationId);
        return $this->db->resultSet();
    }

    /**
     * Get total pending Buckx allocation for an organization (excluding expired ones)
     */
    public function getTotalPendingAllocation($organizationId) {
        $this->db->query("
            SELECT COALESCE(SUM(pt.buckx_allocated), 0) as total_pending
            FROM project_tasks pt
            INNER JOIN projects p ON pt.project_id = p.id
            WHERE p.organization_id = :org_id 
            AND pt.buckx_allocated > 0 
            AND pt.buckx_distributed = 0
            AND pt.created_at > DATE_SUB(NOW(), INTERVAL 1 YEAR)
        ");
        $this->db->bind(':org_id', $organizationId);
        $result = $this->db->single();
        return $result ? floatval($result->total_pending) : 0;
    }

    /**
     * Get available balance (actual balance minus pending allocations)
     */
    public function getAvailableBalance($userId, $userRole = 'organization') {
        $actualBalance = $this->getBalance($userId);
        
        // Only applicable for organizations
        if ($userRole === 'organization') {
            $pendingAllocation = $this->getTotalPendingAllocation($userId);
            return max(0, $actualBalance - $pendingAllocation);
        }
        
        return $actualBalance;
    }

    /**
     * Transfer Buckx reward to a user when task is completed
     * Called when task status is marked as 'done'
     */
    public function transferTaskReward($taskId, $orgId, $userId, $amount) {
        $amount = (float)$amount;
        
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Reward amount must be greater than 0'];
        }

        try {
            // Start transaction
            $this->db->query('START TRANSACTION');

            // Ensure both wallets exist
            $this->ensureWalletExists($orgId, 'organization');
            $this->ensureWalletExists($userId, 'individual');

            // Check if organization has enough balance
            $orgBalance = $this->getBalance($orgId);
            if ($orgBalance < $amount) {
                $this->db->query('ROLLBACK');
                return [
                    'success' => false, 
                    'message' => 'Organization does not have sufficient BuckX balance'
                ];
            }

            // Deduct from organization wallet
            if (!$this->updateBalance($orgId, $amount, 'subtract')) {
                throw new Exception('Failed to deduct from organization wallet');
            }

            // Credit to user wallet
            if (!$this->updateBalance($userId, $amount, 'add')) {
                throw new Exception('Failed to credit user wallet');
            }

            // Create transaction record
            $this->db->query("
                INSERT INTO wallet_transactions 
                (sender_id, receiver_id, amount, note, transaction_type, status, created_at) 
                VALUES (:sender_id, :receiver_id, :amount, :note, 'task_reward', 'completed', NOW())
            ");
            $this->db->bind(':sender_id', $orgId);
            $this->db->bind(':receiver_id', $userId);
            $this->db->bind(':amount', $amount);
            $this->db->bind(':note', "Task Reward - Task ID: {$taskId}");

            if (!$this->db->execute()) {
                throw new Exception('Failed to create transaction record');
            }

            $this->db->query('COMMIT');

            return [
                'success' => true,
                'message' => "Successfully transferred {$amount} BuckX to user",
                'transaction_id' => $this->db->lastInsertId()
            ];

        } catch (Exception $e) {
            $this->db->query('ROLLBACK');
            error_log("Task reward transfer error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to transfer reward: ' . $e->getMessage()
            ];
        }
    }

}
