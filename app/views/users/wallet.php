<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/usersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/wallet.css">

<main class="site-main">  
<div class="dashboard-container">
<div class="dashboard-main">
    
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
            <div class="stat-icon"> &#x1F4B0;</div>
            <div class="stat-value" id="currentBalance"><?= $data['balance'] ?></div>
            <div class="stat-label">Current BuckX Balance</div>
        </div>
        
        <div class="stat-card sent-card">
            <div class="stat-icon">&#x1F4E4;</div>
            <div class="stat-value"><?= $data['totalSent'] ?></div>
            <div class="stat-label">Total Sent BuckX</div>
        </div>
        
        <div class="stat-card received-card">
            <div class="stat-icon">&#x1F4E5;</div>
            <div class="stat-value"><?= $data['totalReceived'] ?></div>
            <div class="stat-label">Total Received BuckX</div>
        </div>
    </div>

    <!-- Transfer Section removed per request -->

    <!-- Transactions Container -->
    <div class="transactions-container user-transactions">
        <!-- Sent Transactions -->
        <div class="transaction-section">
            <div class="transaction-header">
                <div class="transaction-title">
                    <span>&#x1F4E4;</span>
                    Sent Transactions
                </div>
                <div class="transaction-count"><?= count($data['sentTransactions']) ?></div>
            </div>
            <div class="transaction-table">
                <div class="transaction-table-header" aria-hidden="true">
                    <div>To</div>
                    <div>Date</div>
                    <div class="amount">Amount</div>
                </div>
                <div class="transaction-list">
                <?php if (empty($data['sentTransactions'])): ?>
                    <div class="empty-state">
                        <p>No sent transactions yet</p>
                    </div>
                <?php else: ?>
                    <?php foreach($data['sentTransactions'] as $tx): ?>
                        <div class="transaction-item">
                            <div class="tx-user">
                                <?= htmlspecialchars($tx->receiver) ?>
                            </div>
                            <div class="tx-date">
                                <?= htmlspecialchars($tx->timestamp) ?>
                            </div>
                            <div class="transaction-amount sent">
                                -<?= number_format($tx->amount, 2) ?> BuckX
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Received Transactions -->
        <div class="transaction-section">
            <div class="transaction-header">
                <div class="transaction-title">
                    <span>&#x1F4E5;</span>
                    Received Transactions
                </div>
                <div class="transaction-count"><?= count($data['receivedTransactions']) ?></div>
            </div>
            <div class="transaction-table">
                <div class="transaction-table-header" aria-hidden="true">
                    <div>From</div>
                    <div>Date</div>
                    <div class="amount">Amount</div>
                </div>
                <div class="transaction-list">
                <?php if (empty($data['receivedTransactions'])): ?>
                    <div class="empty-state">
                        <p>No received transactions yet</p>
                    </div>
                <?php else: ?>
                    <?php foreach($data['receivedTransactions'] as $tx): ?>
                        <div class="transaction-item">
                            <div class="tx-user">
                                <?= htmlspecialchars($tx->sender) ?>
                            </div>
                            <div class="tx-date">
                                <?= htmlspecialchars($tx->timestamp) ?>
                            </div>
                            <div class="transaction-amount received">
                                +<?= number_format($tx->amount, 2) ?> BuckX
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
</main>

<?php require_once "../app/views/layouts/footer_user.php"; ?>
<script src="<?= URLROOT ?>/assets/js/wallet.js"></script>
