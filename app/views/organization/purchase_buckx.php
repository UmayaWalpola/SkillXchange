<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/purchase_buckx.css">

<main class="site-main">  
<div class="dashboard-container">
<div class="dashboard-main">
<div class="container">
    
    <div class="page-header">
        <h1>Purchase BuckX</h1>
        <p>Choose a package to add more BuckX to your organization wallet</p>
    </div>
    <!-- Success Message -->
<?php if (isset($_SESSION['payment_success'])): 
    $success = $_SESSION['payment_success'];
?>
    <div class="alert alert-success" style="background: #10b981; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="margin: 0 0 10px 0;">✅ Payment Successful!</h3>
        <p style="margin: 5px 0;">
            <strong><?= number_format($success['buckx_amount']) ?> BuckX</strong> has been added to your wallet
        </p>
        <p style="margin: 5px 0; font-size: 14px;">
            Amount Paid: <strong>LKR <?= number_format($success['amount_paid'], 2) ?></strong>
        </p>
        <p style="margin: 10px 0 0 0; font-size: 12px; opacity: 0.9;">
            Transaction ID: <?= htmlspecialchars($success['transaction_id']) ?>
        </p>
    </div>
    <?php unset($_SESSION['payment_success']); ?>
<?php endif; ?>

<!-- Error Message -->
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error" style="background: #ef4444; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="margin: 0 0 10px 0;">❌ Payment Failed</h3>
        <p style="margin: 0;"><?= $_SESSION['error'] ?></p>
    </div>
    <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Current Balance Card -->
    <div class="current-balance-card">
        <div class="balance-info">
            <div class="balance-content">
                <div class="balance-label">Current Balance</div>
                <div class="balance-value"><?= $data['currentBalance'] ?? '0.00' ?> BuckX</div>
            </div>
        </div>
    </div>

    <!-- Packages Grid -->
    <div class="packages-section">
        <h2 class="section-heading">Choose Your Package</h2>
        <div class="packages-grid">
    <?php foreach($data['packages'] as $package): ?>
        <div class="package-card" data-package-id="<?= $package['package_id'] ?>">
            
            
            <h3 class="package-name"><?= htmlspecialchars($package['package_name']) ?></h3>
            
            <div class="package-buckx">
                <span class="buckx-amount"><?= number_format($package['buckx_amount']) ?></span>
                <span class="buckx-label">BuckX</span>
            </div>
            
            <div class="package-price">
                <span class="price-amount"><?= number_format($package['price_lkr'], 2) ?></span>
                <span class="price-label">LKR</span>
            </div>
            
            <button class="btn-select-package" 
                    data-package-id="<?= $package['package_id'] ?>"
                    data-package-name="<?= htmlspecialchars($package['package_name']) ?>"
                    data-buckx-amount="<?= $package['buckx_amount'] ?>"
                    data-price="<?= $package['price_lkr'] ?>"
                    data-currency="LKR">
                Select Package
            </button>
        </div>
    <?php endforeach; ?>
</div>

    </div>

    <!-- Payment Info Box -->
    <div class="payment-info-box">
        <div class="info-icon">🔒</div>
        <div class="info-content">
            <h3>Secure Payment</h3>
            <p>Your payment is processed securely through Stripe. We never store your card details.</p>
        </div>
    </div>

</div>
</div>
</div>
</main>

<?php require_once "../app/views/layouts/footer_user.php"; ?>

<script src="<?= URLROOT ?>/assets/js/purchase_buckx.js"></script>