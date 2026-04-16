<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/usersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/wallet.css">

<main class="site-main">
<div class="dashboard-container">
<div class="dashboard-main">
<div class="container">

    <div class="page-header">
        <h1>Your Wallet</h1>
        <p>Manage your BuckX and transactions</p>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (floatval(str_replace(',', '', $data['balance'])) <= $data['lowBalanceThreshold']): ?>
        <div class="alert alert-warning">Your BuckX balance is running low!</div>
    <?php endif; ?>

    <!-- Balance Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-label">Current Balance</div>
            <div class="stat-value" id="currentBalance"><?= $data['balance'] ?> BuckX</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Sent</div>
            <div class="stat-value"><?= $data['totalSent'] ?> BuckX</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Received</div>
            <div class="stat-value"><?= $data['totalReceived'] ?> BuckX</div>
        </div>
    </div>

    <!-- Skill Debt Summary Cards -->
    <?php
        $totalOwed     = array_sum(array_column($data['debtsOwed'], 'hours_owed'));
        $totalOwedToMe = array_sum(array_column($data['debtsOwedToMe'], 'hours_owed'));
    ?>
    <div class="debt-cards-row">
        <div class="debt-card">
            <div class="debt-card-label">Skill Hours I Owe</div>
            <div class="debt-card-value owe"><?= number_format($totalOwed, 2) ?> hrs</div>
            <div class="debt-card-sub"><?= count($data['debtsOwed']) ?> active debt(s)</div>
        </div>
        <div class="debt-card">
            <div class="debt-card-label">Skill Hours Owed to Me</div>
            <div class="debt-card-value receive"><?= number_format($totalOwedToMe, 2) ?> hrs</div>
            <div class="debt-card-sub"><?= count($data['debtsOwedToMe']) ?> pending debt(s)</div>
        </div>
    </div>

    <!-- Skill Debt Table -->
    <div class="transaction-section">
        <div class="transaction-header">
            <div class="transaction-title">Skill Debts</div>
            <div class="transaction-count"><?= count($data['debtsOwed']) + count($data['debtsOwedToMe']) ?></div>
        </div>

        <?php if (empty($data['debtsOwed']) && empty($data['debtsOwedToMe'])): ?>
            <div class="empty-state"><p>No active skill debts</p></div>
        <?php else: ?>
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>User</th>
                        <th>Skill</th>
                        <th>Status</th>
                        <th>Since</th>
                        <th style="text-align:right;">Hours</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['debtsOwed'] as $debt): ?>
                    <tr>
                        <td><span class="type-label" style="color:#dc2626;">I OWE</span></td>
                        <td><?= htmlspecialchars($debt['creditor_name']) ?></td>
                        <td><?= htmlspecialchars($debt['skill_name']) ?></td>
                        <td><span class="status-badge status-<?= $debt['status'] ?>"><?= ucfirst($debt['status']) ?></span></td>
                        <td><span class="transaction-date"><?= date('M d, Y', strtotime($debt['created_at'])) ?></span></td>
                        <td><div class="transaction-amount sent">-<?= number_format($debt['hours_owed'], 2) ?> hrs</div></td>
                    </tr>
                    <?php endforeach; ?>

                    <?php foreach ($data['debtsOwedToMe'] as $debt): ?>
                    <tr>
                        <td><span class="type-label" style="color:#16a34a;">OWED TO ME</span></td>
                        <td><?= htmlspecialchars($debt['debtor_name']) ?></td>
                        <td><?= htmlspecialchars($debt['skill_name']) ?></td>
                        <td><span class="status-badge status-<?= $debt['status'] ?>"><?= ucfirst($debt['status']) ?></span></td>
                        <td><span class="transaction-date"><?= date('M d, Y', strtotime($debt['created_at'])) ?></span></td>
                        <td><div class="transaction-amount received">+<?= number_format($debt['hours_owed'], 2) ?> hrs</div></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Transaction History Table -->
    <div class="transaction-section">
        <div class="transaction-header">
            <div class="transaction-title">Transaction History</div>
            <div class="transaction-count"><?= count($data['sentTransactions']) + count($data['receivedTransactions']) ?></div>
        </div>

        <?php if (empty($data['sentTransactions']) && empty($data['receivedTransactions'])): ?>
            <div class="empty-state"><p>No transactions yet</p></div>
        <?php else: ?>
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>User / Source</th>
                        <th>Date</th>
                        <th style="text-align:right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['sentTransactions'] as $tx): ?>
                    <tr>
                        <td><span class="type-label" style="color:var(--primary-blue);">SENT</span></td>
                        <td><?= htmlspecialchars($tx->receiver) ?></td>
                        <td><span class="transaction-date"><?= htmlspecialchars($tx->timestamp) ?></span></td>
                        <td><div class="transaction-amount sent">-<?= number_format($tx->amount, 2) ?> BuckX</div></td>
                    </tr>
                    <?php endforeach; ?>

                    <?php foreach ($data['receivedTransactions'] as $tx): ?>
                    <tr>
                        <td>
                            <?php 
                                $txType = $tx->transaction_type ?? 'transfer';
                                if ($txType === 'task_reward') {
                                    echo '<span class="type-label" style="background: linear-gradient(135deg, #3b82f6 0%, #0ea5e9 100%); color: white;">TASK REWARD</span>';
                                } else {
                                    echo '<span class="type-label" style="color:#16a34a;">RECEIVED</span>';
                                }
                            ?>
                        </td>
                        <td>
                            <?php 
                                $txType = $tx->transaction_type ?? 'transfer';
                                if ($txType === 'task_reward') {
                                    // Extract task ID from note if available
                                    echo '<strong>Task Reward</strong><br><small style="color:#666;">' . htmlspecialchars($tx->note ?? '') . '</small>';
                                } else {
                                    echo htmlspecialchars($tx->sender);
                                }
                            ?>
                        </td>
                        <td><span class="transaction-date"><?= htmlspecialchars($tx->timestamp) ?></span></td>
                        <td><div class="transaction-amount received">+<?= number_format($tx->amount, 2) ?> BuckX</div></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>
</div>
</div>
</main>

<?php require_once "../app/views/layouts/footer_user.php"; ?>
<script src="<?= URLROOT ?>/assets/js/wallet.js"></script>