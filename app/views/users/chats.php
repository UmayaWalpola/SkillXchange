<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/usersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/chats.css">

<main class="site-main">
<div class="dashboard-container">
    <div class="dashboard-main">
        
        <div class="chats-page">
            <div class="page-header">
                <h1>Your Chats</h1>
                <p>Connect and communicate with your matches</p>
            </div>

            <div class="chats-layout">
                <!-- Chat List Sidebar -->
                <div class="chat-list">
                    <div class="chat-search">
                        <input type="text" id="searchChats" placeholder="Search conversations..." class="search-input">
                    </div>
                    
                    <div class="conversations">
                        <?php if (!empty($data['allChats'])): ?>
                            <?php foreach ($data['allChats'] as $chat): ?>
                                <a class="chat-item <?= $chat['unread'] ? 'unread' : ''; ?> <?= isset($data['partnerId']) && $chat['partner_id'] == $data['partnerId'] ? 'active' : ''; ?>" 
                                   data-chat-id="<?= $chat['id']; ?>"
                                   data-partner-id="<?= $chat['partner_id']; ?>"
                                   href="<?= URLROOT ?>/chat/user/<?= $chat['partner_id']; ?>"
                                   style="text-decoration: none; color: inherit;">
                                    <div class="chat-avatar">
                                        <?php if (!empty($chat['avatar']) && strpos($chat['avatar'], 'uploads/') === 0): ?>
                                            <img src="<?= URLROOT ?>/<?= htmlspecialchars($chat['avatar']) ?>" alt="Avatar">
                                        <?php else: ?>
                                            <?= htmlspecialchars($chat['avatar']); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="chat-info">
                                        <div class="chat-header-row">
                                            <h3 class="chat-name"><?= htmlspecialchars($chat['name']); ?></h3>
                                            <span class="chat-time"><?= htmlspecialchars($chat['time']); ?></span>
                                        </div>
                                        <div class="chat-preview-row">
                                            <p class="chat-preview"><?= htmlspecialchars($chat['lastMessage']); ?></p>
                                            <?php if ($chat['unread']): ?>
                                                <span class="unread-badge"><?= $chat['unreadCount']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-chats">
                                <p>No conversations yet</p>
                                <small>Connect with matches to start chatting!</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Chat Window -->
                <div class="chat-window" id="chatWindow">
                    <?php if (isset($data['partnerId'])): ?>
                        <!-- Active Chat -->
                        <div class="chat-header">
                            <div class="chat-partner-info">
                                <div class="partner-avatar">
                                    <?php if (!empty($data['partnerAvatar']) && strpos($data['partnerAvatar'], 'uploads/') === 0): ?>
                                        <img src="<?= URLROOT ?>/<?= htmlspecialchars($data['partnerAvatar']) ?>" alt="Avatar">
                                    <?php else: ?>
                                        <?= htmlspecialchars($data['partnerAvatar']); ?>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h3><?= htmlspecialchars($data['partnerName']); ?></h3>
                                    <span class="status-online">Active</span>
                                </div>
                            </div>
                            <div class="chat-actions">
                                <!-- NEW: Transaction Button -->
                                <?php if (!isset($data['activeTransaction'])): ?>
                                    <button class="btn-icon" onclick="openTransactionModal()" title="Create Transaction Offer">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <path d="M12 6v12M6 12h12"></path>
                                        </svg>
                                    </button>
                                <?php endif; ?>
                                
                                <button class="btn-icon" onclick="viewPartnerProfile(<?= $data['partnerId']; ?>)" title="View Profile">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- NEW: Transaction Status Banner -->
                        <?php if (isset($data['activeTransaction'])): ?>
                            <div class="transaction-banner" id="transactionBanner" data-transaction='<?= json_encode($data['activeTransaction']); ?>'>
                                <?php 
                                $tx = $data['activeTransaction'];
                                $status = $tx['status'];
                                $userRole = $tx['user_role'];
                                ?>
                                
                                <?php if ($status === 'pending_learner' || $status === 'pending_teacher'): ?>
                                    <!-- Pending Offer -->
                                    <div class="transaction-pending">
                                        <div class="transaction-info">
                                            <strong>Transaction Offer</strong>
                                            <p>
                                                <?php if ($tx['payment_type'] === 'buckx'): ?>
                                                    <?= number_format($tx['amount'], 2); ?> BuckX
                                                <?php else: ?>
                                                    <?= $tx['skill_debt_hours']; ?> hours of <?= htmlspecialchars($tx['skill_name']); ?>
                                                <?php endif; ?>
                                                • <?= $tx['timeframe_hours']; ?> hour timeframe
                                            </p>
                                        </div>
                                        <div class="transaction-actions">
                                            <?php if ($tx['is_creator']): ?>
                                                <span class="transaction-status">Waiting for response...</span>
                                            <?php else: ?>
                                                <button class="btn-accept" onclick="respondToOffer(<?= $tx['id']; ?>, 'accept')">Accept</button>
                                                <button class="btn-reject" onclick="respondToOffer(<?= $tx['id']; ?>, 'reject')">Reject</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                
                                <?php elseif ($status === 'active'): ?>
                                    <!-- Active Session -->
                                    <div class="transaction-active">
                                        <div class="transaction-info">
                                            <strong>Active Session</strong>
                                            <p>
                                                <?php if ($tx['payment_type'] === 'buckx'): ?>
                                                    <?= number_format($tx['amount'], 2); ?> BuckX
                                                <?php else: ?>
                                                    <?= $tx['skill_debt_hours']; ?> hours
                                                <?php endif; ?>
                                                • Expires: <?= date('M j, g:i A', strtotime($tx['expires_at'])); ?>
                                            </p>
                                        </div>
                                        <div class="transaction-actions">
                                            <?php if ($userRole === 'teacher'): ?>
                                                <button class="btn-primary" onclick="markCompleted(<?= $tx['id']; ?>)">Mark Completed</button>
                                            <?php endif; ?>
                                            <button class="btn-leave" onclick="leaveLesson(<?= $tx['id']; ?>)">Leave Lesson</button>
                                        </div>
                                    </div>
                                
                                <?php elseif ($status === 'teacher_completed'): ?>
                                    <!-- Waiting for Learner Verification -->
                                    <div class="transaction-verification">
                                        <div class="transaction-info">
                                            <strong>Session Completed</strong>
                                            <p>
                                                <?php if ($tx['payment_type'] === 'buckx'): ?>
                                                    <?= number_format($tx['amount'], 2); ?> BuckX
                                                <?php else: ?>
                                                    <?= $tx['skill_debt_hours']; ?> hours
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <div class="transaction-actions">
                                            <?php if ($userRole === 'learner'): ?>
                                                <button class="btn-accept" onclick="verifyCompletion(<?= $tx['id']; ?>, 'agreed')">Agree</button>
                                                <button class="btn-report" onclick="openReportModal(<?= $tx['id']; ?>)">Report Issue</button>
                                            <?php else: ?>
                                                <span class="transaction-status">Waiting for learner verification...</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="messages-container" id="messagesContainer">
                            <!-- Messages will be loaded here by JavaScript -->
                            <div class="loading-messages">Loading messages...</div>
                        </div>

                        <div class="message-input-container">
                            <form id="messageForm" onsubmit="sendMessage(event)">
                                <input type="hidden" id="chatId" value="<?= $data['chatId']; ?>">
                                <input type="text" 
                                       id="messageInput" 
                                       placeholder="Type a message..." 
                                       autocomplete="off"
                                       required>
                                <button type="submit" class="btn-send">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <line x1="22" y1="2" x2="11" y2="13"></line>
                                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                    </svg>
                                    Send
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <!-- Empty State -->
                        <div class="empty-state">
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                            <h2>Select a conversation</h2>
                            <p>Choose a chat from the list to start messaging</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>
</main>

<!-- NEW: Transaction Offer Modal -->
<div class="modal-overlay" id="transactionModal" style="display: none;">
    <div class="modal-container transaction-modal">
        <div class="modal-header">
            <h3>Create Transaction Offer</h3>
            <button class="modal-close" onclick="closeTransactionModal()">×</button>
        </div>
        
        <form id="transactionForm" class="modal-body">
            <div class="form-group">
                <label>Your Role</label>
                <select name="role" id="transactionRole" required>
                    <option value="">Select your role</option>
                    <option value="teacher">I'm teaching (I will receive payment)</option>
                    <option value="learner">I'm learning (I will pay)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Payment Type</label>
                <select name="payment_type" id="paymentType" required>
                    <option value="">Select payment type</option>
                    <option value="buckx">BuckX (Currency)</option>
                    <option value="skillx">SkillX (Skill Debt)</option>
                </select>
            </div>

            <div class="form-group" id="buckxAmountGroup" style="display: none;">
                <label>BuckX Amount</label>
                <input type="number" name="amount" id="buckxAmount" step="0.01" min="0">
                <small>Your balance: <strong><?= number_format($data['buckxBalance'], 2); ?> BuckX</strong></small>
            </div>

            <div class="form-group" id="skillxGroup" style="display: none;">
                <label>Skill Name</label>
                <input type="text" name="skill_name" id="skillName" placeholder="e.g., Web Development">
                
                <label>Hours of Skill Debt</label>
                <input type="number" name="skill_debt_hours" id="skillDebtHours" step="0.5" min="0.5">
                <small>How many hours the learner will owe</small>
            </div>

            <div class="form-group">
                <label>Timeframe (Hours)</label>
                <input type="number" name="timeframe_hours" id="timeframeHours" required min="1" value="24">
                <small>How long until the session must be completed</small>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeTransactionModal()">Cancel</button>
                <button type="submit" class="btn-primary">Send Offer</button>
            </div>
        </form>
    </div>
</div>

<!-- NEW: Report Issue Modal -->
<div class="modal-overlay" id="reportModal" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Report Issue</h3>
            <button class="modal-close" onclick="closeReportModal()">×</button>
        </div>
        
        <form id="reportForm" class="modal-body">
            <input type="hidden" id="reportEventId">
            
            <div class="form-group">
                <label>Describe the issue</label>
                <textarea name="dispute_reason" id="disputeReason" rows="4" required placeholder="Please explain what went wrong..."></textarea>
            </div>

            <p style="color: var(--dark-bg); opacity: 0.7; font-size: 0.9rem; margin: 1rem 0;">
                Your payment will be frozen until an admin reviews this dispute.
            </p>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeReportModal()">Cancel</button>
                <button type="submit" class="btn-danger">Submit Report</button>
            </div>
        </form>
    </div>
</div>

<script>
    const URLROOT = '<?= URLROOT ?>';
    const CURRENT_CHAT_ID = <?= isset($data['chatId']) ? $data['chatId'] : 'null' ?>;
    const CURRENT_USER_ID = <?= $_SESSION['user_id'] ?>;
    const PARTNER_ID = <?= isset($data['partnerId']) ? $data['partnerId'] : 'null' ?>;
</script>
<script src="<?= URLROOT ?>/assets/js/chats.js"></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>