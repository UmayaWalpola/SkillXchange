<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">

<style>
.payment-container {
    max-width: 600px;
    margin: 50px auto;
    padding: 30px;
}

.order-summary-card {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.order-summary-card h2 {
    margin-bottom: 20px;
    color: #333;
    font-size: 24px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.summary-row:last-child {
    border-bottom: none;
}

.summary-row.total {
    font-weight: bold;
    font-size: 20px;
    color: #2563eb;
    margin-top: 10px;
}

.payment-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
}

.btn-large {
    padding: 15px 40px;
    font-size: 16px;
    font-weight: 600;
}

.info-box {
    background: #f0f9ff;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    padding: 20px;
    margin-top: 30px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.info-icon {
    font-size: 32px;
}
</style>

<main class="site-main">  
<div class="dashboard-container">
<div class="dashboard-main">
<div class="payment-container">
    
    <div class="order-summary-card">
        <h2><i class="ph ph-package"></i> Order Summary</h2>
        
        <div class="summary-row">
            <span>Package:</span>
            <span><strong><?= htmlspecialchars($data['package']['package_name']) ?></strong></span>
        </div>
        
        <div class="summary-row">
            <span>BuckX Amount:</span>
            <span><strong><?= number_format($data['package']['buckx_amount']) ?> BuckX</strong></span>
        </div>
        
        <div class="summary-row total">
            <span>Total Amount:</span>
            <span><?= number_format($data['package']['price_lkr'], 2) ?> LKR</span>
        </div>
    </div>

    <div class="payment-actions">
        <button id="proceedToPayment" class="btn btn-primary btn-large">
            Proceed to Payment <i class="ph ph-credit-card"></i>
        </button>
        <a href="<?= URLROOT ?>/wallet/purchaseBuckx" class="btn btn-secondary btn-large">
            Cancel
        </a>
    </div>

    <div class="info-box">
        <div class="info-icon"><i class="ph ph-lock"></i></div>
        <div>
            <strong>Secure Payment via Stripe</strong>
            <p style="margin: 5px 0 0 0; color: #666;">You will be redirected to Stripe's secure payment page to enter your card details. OTP verification may be required by your bank.</p>
        </div>
    </div>

</div>
</div>
</div>
</main>

<?php require_once "../app/views/layouts/footer_user.php"; ?>

<script>
document.getElementById('proceedToPayment').addEventListener('click', function() {
    const button = this;
    button.disabled = true;
    button.textContent = 'Processing...';
    
    fetch('<?= URLROOT ?>/wallet/createCheckoutSession', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'package_id=<?= $data['package']['package_id'] ?>'
    })
    .then(response => response.json())
    .then(data => {
        console.log('Stripe Response:', data);
        
        if (data.success && data.checkout_url) {
            // Redirect to Stripe checkout page
            window.location.href = data.checkout_url;
        } else {
            alert('[ERROR] ' + (data.message || 'Payment initialization failed'));
            button.disabled = false;
            button.textContent = 'Proceed to Payment';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('[ERROR] An error occurred. Please try again.');
        button.disabled = false;
        button.textContent = 'Proceed to Payment';
    });
});
</script>