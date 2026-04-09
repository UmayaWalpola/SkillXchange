<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/wallet.css">

<style>
    /* Organization wallet container - single column layout */
    .org-transactions {
        width: 100%;
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .org-transactions .transaction-section {
        max-width: 100%;
        margin: 0;
        padding: 1.5rem;
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
            <a href="<?= URLROOT ?>/wallet/purchaseBuckx" class="btn-primary" style="margin-top: 0.8rem; padding: 0.6rem 1.2rem; font-size: 0.9rem;">
            <i class="ph ph-credit-card"></i> More BuckX
            </a>
        </div>
        
        <div class="stat-card sent-card">
            <div class="stat-icon"> &#x1F4E4;</div>
            <div class="stat-value"><?= $data['totalSent'] ?></div>
            <div class="stat-label">Total Sent BuckX</div>
        </div>
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
            
            <?php if (empty($data['sentTransactions'])): ?>
                <div class="empty-state">
                    <p>No sent transactions yet</p>
                </div>
            <?php else: ?>
                <table class="transaction-table">
                    <thead>
                        <tr>
                            <th>Recipient</th>
                            <th>Note/Reason</th>
                            <th>Date</th>
                            <th style="text-align: right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['sentTransactions'] as $tx): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($tx->receiver) ?>
                                    <?php if ($tx->receiver_role === 'organization'): ?>
                                        <span class="role-badge">ORG</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($tx->note)): ?>
                                        <?= htmlspecialchars($tx->note) ?>
                                    <?php else: ?>
                                        <span style="opacity: 0.6;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="transaction-date">
                                        <?= htmlspecialchars($tx->timestamp) ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="transaction-amount sent">
                                        -<?= number_format($tx->amount, 2) ?> BuckX
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Received transactions removed for organizations (not applicable) -->
    </div>

</div>
</div>
</div>
</main>

<?php require_once "../app/views/layouts/footer_user.php"; ?>

<script src="<?= URLROOT ?>/assets/js/wallet.js"></script>
