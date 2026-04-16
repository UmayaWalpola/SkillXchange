<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

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
            <i class="ph ph-credit-card"></i> More BuckX
            </a>
        </div>
        
        <div class="stat-card sent-card">
            <div class="stat-icon"> &#x1F4E4;</div>
            <div class="stat-value"><?= $data['totalSent'] ?></div>
            <div class="stat-label">Total Sent BuckX</div>
        </div>
        
        <div class="stat-card received-card">
            <div class="stat-icon">&#x1F4E5;</div>
            <div class="stat-value"><?= $data['totalReceived'] ?></div>
            <div class="stat-label">Total Received BuckX</div>
        </div>
    </div>

    <!-- Pending Task Allocations Section -->
    <div class="transaction-section">
        <div class="transaction-header">
            <div class="transaction-title">Pending Task Allocations (Assigned for Future Transfer)</div>
        </div>
        <div class="transaction-list">
            <?php 
                if (!empty($data['pendingAllocations']) && is_array($data['pendingAllocations'])): 
                    $totalPending = 0;
            ?>
                <table class="transaction-table">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Task</th>
                            <th>Assigned To</th>
                            <th>BuckX Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['pendingAllocations'] as $alloc): ?>
                        <tr>
                            <td><?= htmlspecialchars($alloc->project_name ?? '') ?></td>
                            <td><?= htmlspecialchars($alloc->title ?? '') ?></td>
                            <td><?= htmlspecialchars($alloc->assigned_user ?? 'Unassigned') ?></td>
                            <td><?= (int)$alloc->buckx_allocated ?> BuckX</td>
                            <td><span class="status-badge status-pending">Pending</span></td>
                        </tr>
                        <?php 
                            $totalPending += (int)$alloc->buckx_allocated;
                        endforeach; 
                        ?>
                    </tbody>
                </table>
                <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #d5eaf6; text-align: right;">
                    <strong>Total Pending: </strong>
                    <span style="font-weight: 600;"><?= $totalPending ?> BuckX</span>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>No pending task allocations</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="transaction-section">
        <div class="transaction-header">
            <div class="transaction-title">Transaction History</div>
        </div>
        <div class="transaction-list">
            <?php 
                $allTransactions = array_merge($data['sentTransactions'] ?? [], $data['receivedTransactions'] ?? []);
                usort($allTransactions, function($a, $b) {
                    return strtotime($b->timestamp) - strtotime($a->timestamp);
                });
            ?>
            <?php if (empty($allTransactions)): ?>
                <div class="empty-state">
                    <p>No transactions yet</p>
                </div>
            <?php else: ?>
                <table class="transaction-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Note</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($allTransactions as $tx): ?>
                        <tr>
                            <td>
                                <?= isset($tx->receiver) ? htmlspecialchars($tx->receiver) : htmlspecialchars($tx->sender) ?>
                            </td>
                            <td>
                                <?php if (isset($tx->receiver)): ?>
                                    <span class="type-label" style="color: #dc2626;">Sent</span>
                                <?php else: ?>
                                    <span class="type-label" style="color: #059669;">Received</span>
                                <?php endif; ?>
                            </td>
                            <td class="transaction-amount <?= isset($tx->receiver) ? 'sent' : 'received' ?>">
                                <?php if (isset($tx->receiver)): ?>
                                    -<?= (int)$tx->amount ?> BuckX
                                <?php else: ?>
                                    +<?= (int)$tx->amount ?> BuckX
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($tx->note ?? '-') ?></td>
                            <td class="transaction-date"><?= htmlspecialchars($tx->timestamp) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</div>
</div>
</div>
</main>

<?php require_once "../app/views/layouts/footer_user.php"; ?>

<script src="<?= URLROOT ?>/assets/js/wallet.js"></script>
