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
                    <div class="alert alert-success">
                        <span>✓</span> <strong><?= number_format($success['buckx_amount']) ?> BuckX</strong> has been added to your wallet
                    </div>
                    <?php unset($_SESSION['payment_success']); ?>
                <?php endif; ?>

                <!-- Error Message -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <span>✕</span> <?= $_SESSION['error'] ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- Packages Grid -->
                <div class="packages-section">
                    <h2>Available Packages</h2>
                    <div class="packages-grid">
                <?php foreach($data['packages'] as $package): ?>
                    <div class="package-card">
                        <h3><?= htmlspecialchars($package['package_name']) ?></h3>
                        
                        <div class="package-details">
                            <div class="detail-row">
                                <span class="label">BuckX Amount</span>
                                <span class="value"><?= number_format($package['buckx_amount']) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Price</span>
                                <span class="value">LKR <?= number_format($package['price_lkr'], 2) ?></span>
                            </div>
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
                    <div class="info-icon"><i class="ph ph-lock"></i></div>
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