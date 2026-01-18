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
                        <?php if (!empty($data['chats'])): ?>
                            <?php foreach ($data['chats'] as $chat): ?>
                                <div class="chat-item <?= $chat['unread'] ? 'unread' : ''; ?> <?= isset($data['partnerId']) && $chat['partner_id'] == $data['partnerId'] ? 'active' : ''; ?>" 
                                     data-chat-id="<?= $chat['id']; ?>"
                                     data-partner-id="<?= $chat['partner_id']; ?>"
                                     onclick="openChatWindow(<?= $chat['partner_id']; ?>)">
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
                                </div>
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
                                <button class="btn-icon" onclick="viewPartnerProfile(<?= $data['partnerId']; ?>)" title="View Profile">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                </button>
                            </div>
                        </div>

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

<script>
    const URLROOT = '<?= URLROOT ?>';
    const CURRENT_CHAT_ID = <?= isset($data['chatId']) ? $data['chatId'] : 'null' ?>;
    const CURRENT_USER_ID = <?= $_SESSION['user_id'] ?>;
</script>
<script src="<?= URLROOT ?>/assets/js/chats.js"></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>