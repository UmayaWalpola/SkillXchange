<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/payment_result.css">

<main class="site-main">  
<div class="dashboard-container">
<div class="dashboard-main">
<div class="container">
    
    <div class="result-card success-card">
        <div class="result-icon"><i class="ph ph-check-circle"></i></div>
        <h1 class="result-title">Payment Successful!</h1>
        <p class="result-message">Your BuckX has been added to your wallet</p>
        
        <div class="result-details">
            <div class="detail-row">
                <span class="detail-label">BuckX Added:</span>
                <span class="detail-value"><?= number_format($data['buckx_amount']) ?> BuckX</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Amount Paid:</span>
                <span class="detail-value"><?= number_format($data['amount_paid'], 2) ?> LKR</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Transaction ID:</span>
                <span class="detail-value small"><?= htmlspecialchars($data['transaction_id']) ?></span>
            </div>
        </div>
        
        <div class="result-actions">
            <a href="<?= URLROOT ?>/wallet" class="btn btn-primary">
                View Wallet
            </a>
            <a href="<?= URLROOT ?>/wallet/purchaseBuckx" class="btn btn-secondary">
                Buy More BuckX
            </a>
        </div>
    </div>

</div>
</div>
</div>
</main>

<?php require_once "../app/views/layouts/footer_user.php"; ?>