<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/payment_result.css">

<main class="site-main">  
<div class="dashboard-container">
<div class="dashboard-main">
<div class="container">
    
    <div class="result-card cancel-card">
        <div class="result-icon"><i class="ph ph-x-circle"></i></div>
        <h1 class="result-title">Payment Cancelled</h1>
        <p class="result-message">Your payment was cancelled. No charges were made.</p>
        
        <div class="result-actions">
            <a href="<?= URLROOT ?>/wallet/purchaseBuckx" class="btn btn-primary">
                Try Again
            </a>
            <a href="<?= URLROOT ?>/wallet" class="btn btn-secondary">
                Back to Wallet
            </a>
        </div>
    </div>

</div>
</div>
</div>
</main>

<?php require_once "../app/views/layouts/footer_user.php"; ?>