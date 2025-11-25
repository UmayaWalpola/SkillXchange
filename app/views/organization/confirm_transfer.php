<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/confirm_transfer.css">

<main class="site-main">  
<div class="dashboard-container">
<div class="dashboard-main">
<div class="container">
    
    <div class="page-header">
        <h1>Confirm Transfer</h1>
        <p>Please review the transfer details before confirming</p>
    </div>

    <div class="confirmation-card">
        <div class="warning-banner">
            <span>Please verify all details carefully. This action cannot be undone.</span>
        </div>

        <div class="transfer-details">
            <h2>Transfer Details</h2>
            
            <div class="detail-row">
                <span class="label">Recipient:</span>
                <span class="value recipient-name">
                    <span class="user-icon">
                        <?= ($data['recipient']->role === 'organization') ? '&#x1F465;' : '&#x1F464;' ?>
                    </span>
                    <?= htmlspecialchars($data['recipient']->username) ?>
                    <?php if ($data['recipient']->role === 'organization'): ?>
                        <span class="org-badge">ORGANIZATION</span>
                    <?php endif; ?>
                </span>
            </div>

            <div class="detail-row">
                <span class="label">Amount:</span>
                <span class="value amount-value">
                    <?= number_format($data['amount'], 2) ?> BuckX
                </span>
            </div>

            <?php if (!empty($data['note'])): ?>
            <div class="detail-row">
                <span class="label">Note:</span>
                <span class="value"><?= htmlspecialchars($data['note']) ?></span>
            </div>
            <?php endif; ?>

            <div class="balance-summary">
                <div class="balance-item">
                    <span class="balance-label">Current Balance:</span>
                    <span class="balance-value current"><?= number_format($data['senderBalance'], 2) ?> BuckX</span>
                </div>
                <div class="balance-divider">-</div>
                <div class="balance-item">
                    <span class="balance-label">Transfer Amount:</span>
                    <span class="balance-value transfer"><?= number_format($data['amount'], 2) ?> BuckX</span>
                </div>
                <div class="balance-divider">=</div>
                <div class="balance-item">
                    <span class="balance-label">Remaining Balance:</span>
                    <span class="balance-value remaining <?= $data['remainingBalance'] < 50 ? 'low' : '' ?>">
                        <?= number_format($data['remainingBalance'], 2) ?> BuckX
                    </span>
                </div>
            </div>

            <?php if ($data['remainingBalance'] < 50): ?>
            <div class="low-balance-warning">
                <span class="warning-icon">⚠️</span>
                <span>Warning: Your remaining balance will be low after this transfer</span>
            </div>
            <?php endif; ?>
        </div>

        <form id="confirmTransferForm" method="POST">
            <input type="hidden" name="recipient_id" value="<?= $data['recipient']->id ?>">
            <input type="hidden" name="amount" value="<?= $data['amount'] ?>">
            <input type="hidden" name="note" value="<?= htmlspecialchars($data['note']) ?>">
            
            <div class="action-buttons">
                <button type="button" class="btn btn-cancel" onclick="window.location.href='<?= URLROOT ?>/wallet'">
                 Cancel Transfer
                </button>
                <button type="submit" class="btn btn-confirm">
                 Confirm & Send
                </button>
            </div>
        </form>
    </div>

</div>
</div>
</div>
</main>

<?php require_once "../app/views/layouts/footer_user.php"; ?>
<script src="<?= URLROOT ?>/assets/js/confirm_transfer.js"></script>