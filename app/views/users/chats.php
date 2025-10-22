<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/usersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/chats.css">

<main class="site-main">
<div class="dashboard-container">
    <div class="dashboard-main">
        
        <div class="chats-page">
            <div class="page-header">
                <h1>Your Chat List</h1>
                <p>Connect and communicate</p>
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
                                <?php 
                                // Convert chat data to JSON for JavaScript
                                $chatJson = htmlspecialchars(json_encode($chat), ENT_QUOTES, 'UTF-8');
                                ?>
                                <div class="chat-item <?= $chat['unread'] ? 'unread' : ''; ?>" 
                                     data-chat-id="<?= $chat['id']; ?>" 
                                     onclick='openChat(<?= $chatJson ?>)'>
                                    <div class="chat-avatar"><?= strtoupper(substr($chat['name'], 0, 2)); ?></div>
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
                                <small>Start chatting with your matches!</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Chat Window -->
                <div class="chat-window" id="chatWindow">
                    <div class="empty-state">
                        <h2>Select a conversation</h2>
                        <p>Choose a chat from the list to start messaging</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
</main>

<script src="<?= URLROOT ?>/assets/js/chats.js"></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>