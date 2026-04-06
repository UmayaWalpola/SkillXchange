<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/organizations.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/reporting.css">

<main class="site-main">
    <div class="chats-container">
        <div class="page-header">
            <h1>Project Chats</h1>
            <p>Communicate with your project teams</p>
        </div>

        <div class="chat-layout">
            <div class="chat-area" style="flex:1;">
                <?php if (!empty($data['project'])): ?>
                    <div class="chat-header">
                        <div class="chat-project-info">
                            <div class="project-avatar web"><i class="ph ph-code"></i></div>
                            <div>
                                <h3 class="chat-project-name" id="chatProjectName"><?= htmlspecialchars($data['project']->name) ?></h3>
                                <p class="members-count" id="membersCount"><?= count($data['members'] ?? []) ?> members</p>
                            </div>
                        </div>
                    </div>

                    <div class="messages-area" id="messagesArea">
                        <div class="empty-state" id="emptyState">No messages yet — start the conversation!</div>
                    </div>

                    <div class="message-input-container">
                        <textarea id="chatInput" class="message-input" rows="2" placeholder="Type your message..."></textarea>
                        <button id="sendBtn" class="send-btn">Send</button>
                    </div>
                    <div id="typingIndicator" class="text-muted" style="margin-top:4px;font-size:0.8rem;display:none;">Someone is typing…</div>
                <?php else: ?>
                    <div class="chat-empty-state">
                        <h3>No Projects Yet</h3>
                        <p>Create a project to start chatting with your team</p>
                        <button class="create-btn" onclick="window.location.href='<?= URLROOT ?>/organization/projects'">Create Project</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script>
const CHAT_PROJECT_ID = <?= isset($data['projectId']) ? (int)$data['projectId'] : 0 ?>;
let lastMessageCount = 0;
let typingTimeout = null;

function renderMessages(payload) {
    const area = document.getElementById('messagesArea');
    const emptyState = document.getElementById('emptyState');
    if (!area) return;

    const messages = payload.messages || [];
    const currentUserId = payload.current_user_id;

    area.innerHTML = '';
    if (messages.length === 0) {
        if (emptyState) emptyState.style.display = 'block';
        area.appendChild(emptyState);
        return;
    }

    if (emptyState) emptyState.style.display = 'none';

    messages.forEach(m => {
        const isMine = (parseInt(m.sender_id, 10) === parseInt(currentUserId, 10));
        const wrapper = document.createElement('div');
        wrapper.className = 'chat-message-row ' + (isMine ? 'mine' : 'theirs');

        const bubble = document.createElement('div');
        bubble.className = 'chat-bubble ' + (isMine ? 'mine' : 'theirs');

        const header = document.createElement('div');
        header.className = 'chat-bubble-header';
        header.textContent = m.sender_name;

        const body = document.createElement('div');
        body.className = 'chat-bubble-body';
        body.textContent = m.message;

        const meta = document.createElement('div');
        meta.className = 'chat-bubble-meta';
        meta.textContent = m.created_at;

        // Add report button for messages from other users
        if (!isMine) {
            const reportBtn = document.createElement('button');
            reportBtn.className = 'report-btn-small report-content-btn';
            reportBtn.setAttribute('data-content-type', 'chat_message');
            reportBtn.setAttribute('data-content-id', m.id);
            reportBtn.title = 'Report this message';
            reportBtn.style.marginTop = '0.5rem';
            reportBtn.innerHTML = '<span><i class="ph ph-warning"></i></span>';
            bubble.appendChild(reportBtn);
        }

        bubble.appendChild(header);
        bubble.appendChild(body);
        bubble.appendChild(meta);
        wrapper.appendChild(bubble);
        area.appendChild(wrapper);
    });

    area.scrollTop = area.scrollHeight;
}

function fetchMessages() {
    if (!CHAT_PROJECT_ID) return;
    fetch(`${window.URLROOT}/chat/fetchMessages?project_id=${CHAT_PROJECT_ID}`, { credentials: 'same-origin' })
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            renderMessages(data);
        })
        .catch(() => {});
}

function sendMessage() {
    const input = document.getElementById('chatInput');
    if (!input) return;
    const msg = input.value.trim();
    if (!msg || !CHAT_PROJECT_ID) return;

    fetch(`${window.URLROOT}/chat/sendMessage`, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `project_id=${encodeURIComponent(CHAT_PROJECT_ID)}&message=${encodeURIComponent(msg)}`
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) return;
        input.value = '';
        fetchMessages();
    })
    .catch(() => {});
}

document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('sendBtn');
    const input = document.getElementById('chatInput');
    if (btn) btn.addEventListener('click', sendMessage);
    if (input) {
        input.addEventListener('keydown', e => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
        input.addEventListener('input', () => {
            const ind = document.getElementById('typingIndicator');
            if (!ind) return;
            ind.style.display = 'block';
            if (typingTimeout) clearTimeout(typingTimeout);
            typingTimeout = setTimeout(() => { ind.style.display = 'none'; }, 1000);
        });
    }

    setInterval(fetchMessages, 2000);
    fetchMessages();
});

// Define URLROOT for reporting system
window.URLROOT = '<?= URLROOT ?>';
</script>

<script src="<?= URLROOT ?>/assets/js/reporting.js"></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>