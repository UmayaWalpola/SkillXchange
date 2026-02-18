<?php

class BuckxPurchase {
    
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    //Create a new purchase record
    public function createPurchase($userId, $packageId, $buckxAmount, $priceLKR) {
        try {
            $this->db->query("INSERT INTO buckx_purchases 
                          (org_id, package_id, buckx_amount, price_lkr, status, payment_method) 
                          VALUES (:org_id, :package_id, :buckx_amount, :price_lkr, 'pending', 'stripe')");
            
            $this->db->bind(':org_id', $userId);
            $this->db->bind(':package_id', $packageId);
            $this->db->bind(':buckx_amount', $buckxAmount);
            $this->db->bind(':price_lkr', $priceLKR);
            
            $this->db->execute();
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating purchase: " . $e->getMessage());
            return false;
        }
    }
    
    //Update purchase with Stripe session ID
    public function updateStripeSession($purchaseId, $sessionId) {
        try {
            $this->db->query("SHOW COLUMNS FROM buckx_purchases LIKE 'stripe_session_id'");
            $this->db->execute();
            
            if ($this->db->rowCount() > 0) {
                $this->db->query("UPDATE buckx_purchases 
                              SET stripe_session_id = :session_id, status = 'pending' 
                              WHERE id = :id");
                $this->db->bind(':session_id', $sessionId);
                $this->db->bind(':id', $purchaseId);
                return $this->db->execute();
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Error updating stripe session: " . $e->getMessage());
            return false;
        }
    }
    
    //Complete purchase (mark as completed)
    public function completePurchase($purchaseId, $paymentIntentId = null) {
        try {
            $this->db->query("UPDATE buckx_purchases 
                          SET status = 'completed', 
                              transaction_id = :transaction_id, 
                              completed_at = NOW()
                          WHERE id = :id");
            
            $this->db->bind(':transaction_id', $paymentIntentId);
            $this->db->bind(':id', $purchaseId);
            
            return $this->db->execute();
        } catch (PDOException $e) {
            error_log("Error completing purchase: " . $e->getMessage());
            return false;
        }
    }
    
    //Mark purchase as failed
    public function failPurchase($purchaseId) {
        try {
            $this->db->query("UPDATE buckx_purchases 
                          SET status = 'failed' 
                          WHERE id = :id");
            
            $this->db->bind(':id', $purchaseId);
            return $this->db->execute();
        } catch (PDOException $e) {
            error_log("Error failing purchase: " . $e->getMessage());
            return false;
        }
    }
    
    //Get purchase by ID
    public function getPurchaseById($purchaseId) {
        try {
            $this->db->query("SELECT * FROM buckx_purchases WHERE id = :id");
            $this->db->bind(':id', $purchaseId);
            return $this->db->single();
        } catch (PDOException $e) {
            error_log("Error getting purchase: " . $e->getMessage());
            return null;
        }
    }
    
    //Get purchase by Stripe session ID
    public function getPurchaseBySessionId($sessionId) {
        try {
            $this->db->query("SHOW COLUMNS FROM buckx_purchases LIKE 'stripe_session_id'");
            $this->db->execute();
            
            if ($this->db->rowCount() > 0) {
                $this->db->query("SELECT * FROM buckx_purchases WHERE stripe_session_id = :session_id");
                $this->db->bind(':session_id', $sessionId);
                $result = $this->db->single();
                
                if ($result) {
                    return (array) $result;
                }
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Error getting purchase by session: " . $e->getMessage());
            return null;
        }
    }
    
    //Get user's purchase history
    public function getUserPurchases($userId, $limit = 10) {
        try {
            $this->db->query("SELECT * FROM buckx_purchases 
                          WHERE org_id = :org_id 
                          ORDER BY created_at DESC 
                          LIMIT :limit");
            
            $this->db->bind(':org_id', $userId);
            $this->db->bind(':limit', $limit);
            
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log("Error getting user purchases: " . $e->getMessage());
            return [];
        }
    }

    //Log payment event
    public function logPaymentEvent($userId, $purchaseId, $eventType, $eventData = null) {
        try {
            $this->db->query("SHOW TABLES LIKE 'payment_logs'");
            $this->db->execute();
            
            if ($this->db->rowCount() > 0) {
                $this->db->query("INSERT INTO payment_logs 
                              (purchase_id, user_id, event_type, event_data, ip_address, user_agent) 
                              VALUES (:purchase_id, :user_id, :event_type, :event_data, :ip_address, :user_agent)");
                
                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
                $eventDataJson = $eventData ? json_encode($eventData) : null;
                
                $this->db->bind(':purchase_id', $purchaseId);
                $this->db->bind(':user_id', $userId);
                $this->db->bind(':event_type', $eventType);
                $this->db->bind(':event_data', $eventDataJson);
                $this->db->bind(':ip_address', $ipAddress);
                $this->db->bind(':user_agent', $userAgent);
                
                return $this->db->execute();
            }
            
            error_log("Payment Event: $eventType for purchase $purchaseId");
            return true;
        } catch (PDOException $e) {
            error_log("Error logging payment event: " . $e->getMessage());
            return false;
        }
    }
    
    //Add BuckX balance to wallet after successful payment
    public function addBuckxToWallet($orgId, $buckxAmount) {
        try {
            $this->db->query("UPDATE wallets SET balance = balance + :amount WHERE user_id = :user_id");
            $this->db->bind(':amount', abs($buckxAmount));
            $this->db->bind(':user_id', $orgId);
            return $this->db->execute();
        } catch (PDOException $e) {
            error_log("Error adding BuckX to wallet: " . $e->getMessage());
            return false;
        }
    }
}