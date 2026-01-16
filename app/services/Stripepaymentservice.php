<?php

class StripePaymentService {
    private $secretKey;
    private $apiUrl;
    
    public function __construct() {    
        // Load secrets from config file
        $secrets = require_once __DIR__ . '/../config/secrets.php';
        $this->secretKey = $secrets['stripe_secret_key'];
        
        
        $this->apiUrl = 'https://api.stripe.com';
    }
    
    //Create a Stripe Checkout Session
    //This redirects user to Stripe's payment page
    public function createCheckoutSession($amount, $currency, $successUrl, $cancelUrl, $metadata = []) {
        try {
            $url = $this->apiUrl . '/v1/checkout/sessions';
            
            // Prepare session data
            $data = [
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($currency),
                        'product_data' => [
                            'name' => $metadata['package_name'] ?? 'BuckX Purchase',
                            'description' => $metadata['description'] ?? 'Purchase BuckX for SkillXchange',
                        ],
                        'unit_amount' => intval($amount * 100), // Convert to cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'metadata' => $metadata,
            ];
            
            // Make API request to Stripe
            $response = $this->makeRequest('POST', $url, $data);
            
            if ($response && isset($response['id'])) {
                return [
                    'success' => true,
                    'session_id' => $response['id'],
                    'checkout_url' => $response['url']
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to create checkout session'
            ];
            
        } catch (Exception $e) {
            error_log("Stripe checkout session error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error creating checkout session: ' . $e->getMessage()
            ];
        }
    }
    
    //Retrieve a Stripe Checkout Session
    //This verifies that payment was successful
    public function retrieveSession($sessionId) {
        try {
            $url = $this->apiUrl . '/v1/checkout/sessions/' . $sessionId;
            
            $response = $this->makeRequest('GET', $url);
            
            if ($response && isset($response['id'])) {
                return [
                    'success' => true,
                    'session' => $response
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Session not found'
            ];
            
        } catch (Exception $e) {
            error_log("Stripe retrieve session error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error retrieving session: ' . $e->getMessage()
            ];
        }
    }
    
    //Make HTTP request to Stripe API using cURL
    private function makeRequest($method, $url, $data = null) {
        $ch = curl_init();
        
        // Set URL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // Set authentication (Stripe uses Basic Auth with API key as username)
        curl_setopt($ch, CURLOPT_USERPWD, $this->secretKey . ':');
        
        // Set method
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        }
        
        // Set headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        // Handle response
        if ($httpCode >= 200 && $httpCode < 300) {
            return json_decode($response, true);
        }
        
        // Log error
        error_log("Stripe API Error ($httpCode): " . $response);
        return null;
    }
    
    //Verify webhook signature (for future use)
    public function verifyWebhookSignature($payload, $signature, $secret) {
        // This will be useful when implementing webhooks
        // For now, we verify payments by retrieving the session
        return true;
    }
}