<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/wallet.css">

<style>
    /* Center transactions container only for organization wallet page */
    /* Keep the transactions block inside the main content area so it aligns with the cards above */
    .org-transactions {
        width: 100%;
        margin: 0;
        padding: 0 1rem 0 250px; /* offset for fixed sidebar */
        box-sizing: border-box;
    }

    /* Slightly larger transaction items for organization view */
    .org-transactions .transaction-item {
        padding: 1rem 1.25rem;
        font-size: 1.06rem;
    }

    .org-transactions .transaction-amount {
        font-size: 1.12rem;
        font-weight: 600;
    }
    /* Center the inner content of the full-bleed area */
    .org-transactions .transaction-section {
        max-width: none;
        margin: 0;
        padding: 0;
    }

    /* Make each transaction row span the full viewport (full-bleed) */
    .org-transactions .transaction-list {
        display: block;
        gap: 0.75rem;
    }

    .org-transactions .transaction-item {
        width: 100%;
        box-sizing: border-box;
        padding-left: 2rem;
        padding-right: 2rem;
        display: block;
    }

    /* Inner wrapper to center content inside full-bleed rows */
    /* Match transaction width to top stat-cards area */
    .org-transactions .transaction-inner {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        box-sizing: border-box;
    }

    /* Fallback for small or narrow screens */
    @media (max-width: 1400px) {
        .org-transactions .transaction-inner {
            left: 0;
            margin: 0 1rem;
            padding: 0 1rem;
        }
    }
</style>

<main class="site-main">  
<div class="dashboard-container">
<div class="dashboard-main">
<div class="container">
    
    <div class="page-header">
        <h1>Your Wallet</h1>
        <p>Manage your BuckX and transactions</p>
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

    <!-- Low Balance Warning -->
    <?php if (floatval(str_replace(',', '', $data['balance'])) <= $data['lowBalanceThreshold']): ?>
        <div class="alert alert-warning">
            <span>&#x26A0;&#xFE0F;</span> Your BuckX balance is running low!
        </div>
    <?php endif; ?>
    
    <!-- Top Statistics Row -->
    <div class="stats-row">
        <div class="stat-card balance-card">
            <div class="stat-icon">&#x1F4B0;</div>
            <div class="stat-value" id="currentBalance"><?= $data['balance'] ?></div>
            <div class="stat-label">Current BuckX Balance</div>
            <a href="<?= URLROOT ?>/wallet/purchaseBuckx" class="btn-primary" style="margin-top: 1rem; padding: 0.7rem 1.5rem; font-size: 0.95rem;">
            💳 More BuckX
            </a>
        </div>
        
        <div class="stat-card sent-card">
            <div class="stat-icon"> &#x1F4E4;</div>
            <div class="stat-value"><?= $data['totalSent'] ?></div>
            <div class="stat-label">Total Sent BuckX</div>
        </div>
        
        <!-- Received transactions not applicable for organizations; removed -->
    </div>

    <!-- Transfer Section removed per request -->

    <!-- Transactions Container -->
    <div class="transactions-container org-transactions">
        <!-- Sent Transactions -->
        <div class="transaction-section">
            <div class="transaction-header">
                <div class="transaction-title">
                    <span>&#x1F4E4;</span>
                    Sent Transactions
                </div>
                <div class="transaction-count"><?= count($data['sentTransactions']) ?></div>
            </div>
            <div class="transaction-list">
                <?php if (empty($data['sentTransactions'])): ?>
                    <div class="empty-state">
                        <p>No sent transactions yet</p>
                    </div>
                <?php else: ?>
                    <?php foreach($data['sentTransactions'] as $tx): ?>
                        <div class="transaction-item">
                            <div class="transaction-inner">
                                <div class="transaction-info">
                                    <div class="transaction-user">
                                        <span class="user-icon">
                                            <?= ($tx->receiver_role === 'organization') ? '&#x1F465;' : '&#x1F464;' ?>
                                        </span>
                                        <?= htmlspecialchars($tx->receiver) ?>
                                        <?php if ($tx->receiver_role === 'organization'): ?>
                                            <span class="role-badge">ORG</span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (!empty($tx->note)): ?>
                                        <div class="transaction-note">
                                            <strong>Reason:</strong> <?= htmlspecialchars($tx->note) ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="transaction-time">
                                        <?= htmlspecialchars($tx->timestamp) ?>
                                    </div>
                                </div>
                                <div class="transaction-amount sent">
                                    -<?= number_format($tx->amount, 2) ?> BuckX
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Received transactions removed for organizations (not applicable) -->
    </div>

</div>
</div>
</div>
</main>

<?php require_once "../app/views/layouts/footer_user.php"; ?>

<script src="<?= URLROOT ?>/assets/js/wallet.js"></script>
