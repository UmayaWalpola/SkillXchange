<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/wallet.css">

<main class="site-main">  
<div class="dashboard-container">
<div class="dashboard-main">
<div class="container">
    
    <div class="page-header">
        <h1>Buy BuckX</h1>
        <p>Purchase BuckX (1 BuckX = LKR 100)</p>
    </div>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <span>&#x2705;</span> <?= $_SESSION['success'] ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <span>&#x274C;</span> <?= $_SESSION['error'] ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Purchase Form -->
    <div class="transfer-section">
        <div class="section-header">
            <div>
                <div class="section-title">Complete Your Purchase</div>
                <div class="section-subtitle">Enter purchase details and payment information</div>
            </div>
        </div>

        <form class="transfer-form" id="purchaseForm" method="POST" action="<?= URLROOT ?>/wallet/processPurchase">
            
            <!-- BuckX Amount Section -->
            <div class="form-group full-width">
                <label for="buckx_amount">
                    BuckX Amount
                </label>
                <input type="number" 
                       id="buckx_amount" 
                       name="buckx_amount" 
                       min="100" 
                       step="100"
                       placeholder="Enter amount (Minimum 100 BuckX)" 
                       required>
                <div class="field-hint">Minimum purchase: 100 BuckX = LKR 10,000</div>
            </div>

            <!-- Total Price Display -->
            <div class="purchase-summary">
                <div class="summary-row">
                    <span>BuckX Amount:</span>
                    <strong id="displayAmount">0</strong>
                </div>
                <div class="summary-row">
                    <span>Rate:</span>
                    <strong>LKR 100 per BuckX</strong>
                </div>
                <div class="summary-row total-row">
                    <span>Total Price:</span>
                    <strong id="totalPrice">LKR 0</strong>
                </div>
            </div>

            <!-- Payment Details Section -->
            <div class="payment-section-header">
                 Payment Information
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="card_holder">
                        Cardholder Name
                    </label>
                    <input type="text" 
                           id="card_holder" 
                           name="card_holder" 
                           placeholder="John Doe"
                           required>
                </div>

                <div class="form-group">
                    <label for="card_number">
                        Card Number
                    </label>
                    <input type="text" 
                           id="card_number" 
                           name="card_number" 
                           placeholder="1234 5678 9012 3456"
                           maxlength="19"
                           required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="expiry_date">
                        Expiry Date
                    </label>
                    <input type="text" 
                           id="expiry_date" 
                           name="expiry_date" 
                           placeholder="MM/YY"
                           maxlength="5"
                           required>
                </div>

                <div class="form-group">
                    <label for="cvv">
                        CVV
                    </label>
                    <input type="text" 
                           id="cvv" 
                           name="cvv" 
                           placeholder="123"
                           maxlength="3"
                           required>
                </div>
            </div>

            <button type="submit" class="send-btn" id="payBtn" disabled>
                <span>Complete Purchase</span>
            </button>

            <div class="field-hint" style="text-align: center; margin-top: 1rem;">
                Your payment information is secure and encrypted
            </div>
        </form>
    </div>

</div>
</div>
</div>
</main>

<?php require_once "../app/views/layouts/footer_user.php"; ?>

<script>
// Calculate price on input
const buckxInput = document.getElementById('buckx_amount');
const displayAmount = document.getElementById('displayAmount');
const totalPrice = document.getElementById('totalPrice');
const payBtn = document.getElementById('payBtn');

buckxInput.addEventListener('input', function() {
    const amount = parseInt(this.value) || 0;
    const price = amount * 100;
    
    displayAmount.textContent = amount.toLocaleString() + ' BuckX';
    totalPrice.textContent = 'LKR ' + price.toLocaleString();
    
    // Enable button if amount is valid
    if (amount >= 100) {
        payBtn.disabled = false;
        payBtn.style.opacity = '1';
        payBtn.style.cursor = 'pointer';
    } else {
        payBtn.disabled = true;
        payBtn.style.opacity = '0.5';
        payBtn.style.cursor = 'not-allowed';
    }
});

// Format card number with spaces
document.getElementById('card_number').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s/g, '');
    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
    e.target.value = formattedValue;
});

// Format expiry date
document.getElementById('expiry_date').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.slice(0, 2) + '/' + value.slice(2, 4);
    }
    e.target.value = value;
});

// Only allow numbers in CVV
document.getElementById('cvv').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/\D/g, '');
});
</script>