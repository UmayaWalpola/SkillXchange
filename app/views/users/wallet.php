<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/usersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/wallet.css">

<main class="site-main">  
<div class="dashboard-container">
<div class="dashboard-main">
<div class="container">
    
    <!-- Top Statistics Row -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon">&#128176;</div>
            <div class="stat-value"><?= $data['balance'] ?></div>
            <div class="stat-label">BuckX Balance</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">&#128228;</div>
            <div class="stat-value"><?= $data['totalSent'] ?></div>
            <div class="stat-label">Total Sent BuckX</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">&#128229;</div>
            <div class="stat-value"><?= $data['totalReceived'] ?></div>
            <div class="stat-label">Total Received BuckX</div>
        </div>
    </div>

    <!-- Transfer Section -->
    <div class="transfer-section">
        <div class="section-header">
            <span class="section-icon">💸</span>
            <div>
                <div class="section-title">Send BuckX</div>
                <div style="color: #666; font-size: 14px; margin-top: 4px;">Transfer money to another user</div>
            </div>
        </div>
        
        <form class="transfer-form" id="transferForm">
            <div class="form-group">
                <label for="recipient">👤 Recipient</label>
                <input type="text" id="recipient" name="recipient" placeholder="Enter username" required>
            </div>
            
            <div class="form-group">
                <label for="amount">💰 Amount</label>
                <input type="number" id="amount" name="amount" min="1" placeholder="Enter amount" required>
            </div>
            
            <div class="form-group">
                <label for="note">📝 Note (Optional)</label>
                <input type="text" id="note" name="note" placeholder="Add a note">
            </div>
            
            <button type="submit" class="send-btn">
                <span>💸</span> <span>Send Now</span>
            </button>
        </form>
    </div>

    <!-- Transactions Container -->
    <div class="transactions-container">
        <!-- Sent Transactions -->
        <div class="transaction-section">
            <div class="transaction-header">
                <div class="transaction-title">
                    <span style="margin-right: 10px;">📤</span>
                    Sent Transactions
                </div>
                <div class="transaction-count"><?= count($data['sentTransactions']) ?></div>
            </div>
            <div class="transaction-list">
                <?php foreach($data['sentTransactions'] as $tx): ?>
                    <div class="transaction-item">
                        <div class="transaction-info">
                            <div class="transaction-user"><?= htmlspecialchars($tx['receiver']) ?></div>
                            <div class="transaction-time"><?= htmlspecialchars($tx['timestamp']) ?></div>
                        </div>
                        <div class="transaction-amount sent">-<?= $tx['amount'] ?> BuckX</div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Received Transactions -->
        <div class="transaction-section">
            <div class="transaction-header">
                <div class="transaction-title">
                    <span style="margin-right: 10px;">📥</span>
                    Received Transactions
                </div>
                <div class="transaction-count"><?= count($data['receivedTransactions']) ?></div>
            </div>
            <div class="transaction-list">
                <?php foreach($data['receivedTransactions'] as $tx): ?>
                    <div class="transaction-item">
                        <div class="transaction-info">
                            <div class="transaction-user"><?= htmlspecialchars($tx['sender']) ?></div>
                            <div class="transaction-time"><?= htmlspecialchars($tx['timestamp']) ?></div>
                        </div>
                        <div class="transaction-amount received">+<?= $tx['amount'] ?> BuckX</div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div>
</div>
</div>
</main>

<?php require_once "../app/views/layouts/footer_user.php"; ?>