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
            💳 More BuckX
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

    <!-- Transfer Section -->
    <div class="transfer-section">
        <div class="section-header">
            <div>
                <div class="section-title">Send BuckX</div>
                <div class="section-subtitle">Transfer BuckX to your project contributors</div>
            </div>
        </div>
        
        <?php if (empty($data['allowedRecipients'])): ?>
            <div class="no-recipients-notice">
                <span class="notice-icon">&#x2139;&#xFE0F;</span>
                <div class="notice-content">
                    <h3>No Users Available</h3>
                    <p>There are no users in the system yet.</p>
                </div>
            </div>
        <?php else: ?>
            <form class="transfer-form" id="transferForm" method="POST" action="<?= URLROOT ?>/wallet/confirmTransfer">
                <div class="form-row">
                    <div class="form-group">
                        <label for="recipient_id">Select User</label>
                        <select id="recipient_id" 
                                name="recipient_id" 
                                required
                                class="recipient-dropdown">
                            <option value="">Select a user</option>
                            <?php foreach($data['allowedRecipients'] as $recipient): ?>
                                <option value="<?= $recipient->id ?>">
                                    <?= htmlspecialchars($recipient->username) ?> 
                                    (<?= htmlspecialchars($recipient->email) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="field-hint">Select the user you want to send BuckX to</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="amount">Amount (BuckX)</label>
                        <input type="number" 
                               id="amount" 
                               name="amount" 
                               min="1" 
                               max="1000"
                               step="0.01"
                               placeholder="Enter amount" 
                               required>
                        <div class="field-hint">Maximum: 1000 BuckX per transaction</div>
                    </div>
                </div>
                
                <div class="form-group full-width">
                    <label for="note">Reason for Transfer (Optional)</label>
                    <input type="text" 
                           id="note" 
                           name="note" 
                           placeholder="e.g., Payment for project work, Performance bonus, etc."
                           maxlength="255">
                    <div class="field-hint">This reason will be visible to the recipient</div>
                </div>
                
                <button type="submit" class="send-btn">
                <span>Review Transfer</span>
                </button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Transactions Container -->
    <div class="transactions-container">
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
                    <?php endforeach; ?>
                <?php endif; ?>
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
            <div class="transaction-list">
                <?php if (empty($data['receivedTransactions'])): ?>
                    <div class="empty-state">
                        <p>No received transactions yet</p>
                    </div>
                <?php else: ?>
                    <?php foreach($data['receivedTransactions'] as $tx): ?>
                        <div class="transaction-item">
                            <div class="transaction-info">
                                <div class="transaction-user">
                                    <span class="user-icon">
                                        <?= ($tx->sender_role === 'organization') ? '&#x1F465;' : '&#x1F464;' ?>
                                    </span>
                                    <?= htmlspecialchars($tx->sender) ?>
                                    <?php if ($tx->sender_role === 'organization'): ?>
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
