<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/organizations.css">

<main class="site-main">
    <div class="chats-container">
        
        <!-- Page Header -->
        <div class="page-header">
            <h1>Project Chats</h1>
            <p>Communicate with your project teams</p>
        </div>

        <!-- Chat Layout -->
        <div class="chat-layout">
            <!-- Projects Sidebar -->
            <div class="projects-sidebar">
                <div class="sidebar-header">
                    <h3>Your Projects</h3>
                    <input type="text" class="search-input" placeholder="Search..." id="projectSearch">
                </div>

                <div class="projects-list" id="projectsList">
                    <?php if(!empty($data['projects'])): ?>
                        <?php foreach($data['projects'] as $index => $project): ?>
                            <div class="project-item <?= $index === 0 ? 'active' : '' ?>" 
                                 onclick="selectProject(<?= $project['id'] ?>)">
                                <div class="project-avatar <?= $project['category'] ?>">
                                    <?php 
                                        $icons = [
                                            'web' => '💻',
                                            'mobile' => '📱',
                                            'data' => '📊',
                                            'design' => '🎨'
                                        ];
                                        echo $icons[$project['category']] ?? '💻';
                                    ?>
                                </div>
                                <div class="project-details">
                                    <h4 class="project-name"><?= htmlspecialchars($project['name']) ?></h4>
                                    <p class="last-message"><?= htmlspecialchars($project['last_message'] ?? 'No messages yet') ?></p>
                                </div>
                                <?php if($project['unread_count'] > 0): ?>
                                    <div class="project-badge"><?= $project['unread_count'] ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Chat Area -->
            <div class="chat-area">
                <?php if(!empty($data['projects'])): ?>
                    <!-- Chat Header -->
                    <div class="chat-header">
                        <div class="chat-project-info">
                            <div class="project-avatar web">💻</div>
                            <div>
                                <h3 class="chat-project-name" id="chatProjectName">Select a project</h3>
                                <p class="members-count" id="membersCount">0 members online</p>
                            </div>
                        </div>
                        <div class="chat-actions">
                            <button class="icon-btn" title="Project Details" onclick="showProjectDetails()">ℹ️</button>
                            <button class="icon-btn" title="Members" onclick="toggleMembers()">👥</button>
                        </div>
                    </div>

                    <!-- Messages Area -->
                    <div class="messages-area" id="messagesArea">
                        <!-- Messages will be loaded dynamically via JavaScript -->
                    </div>

                    <!-- Message Input -->
                    <div class="message-input-container">
                        <button class="attachment-btn" title="Attach file">📎</button>
                        <input type="text" class="message-input" placeholder="Type your message..." id="messageInput">
                        <button class="send-btn" onclick="sendMessage()">Send</button>
                    </div>
                <?php else: ?>
                    <!-- Empty State -->
                    <div class="chat-empty-state">
                        <h3>No Projects Yet</h3>
                        <p>Create a project to start chatting with your team</p>
                        <button class="create-btn" onclick="window.location.href='<?= URLROOT ?>/organization/projects'">
                            Create Project
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Members Sidebar (Optional - can be toggled) -->
            <div class="members-sidebar" id="membersSidebar" style="display: none;">
                <div class="sidebar-header">
                    <h3>Project Members</h3>
                    <button class="close-btn" onclick="toggleMembers()">&times;</button>
                </div>

                <div class="members-list" id="membersList">
                    <!-- Members will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>
</main>

<script src="<?= URLROOT ?>/assets/js/organizations.js" defer></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>