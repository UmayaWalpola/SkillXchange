<!-- Sidebar Dashboard -->
<nav class="sidebar">
    <a href="<?= URLROOT ?>/userdashboard" class="sidebar-item <?= (isset($page) && $page == 'profile') ? 'active' : '' ?>">
        <span class="icon"><i class="ph ph-user"></i></span>
        <span>Profile</span>
    </a>

    <a href="<?= URLROOT ?>/userdashboard/matches" class="sidebar-item <?= (isset($page) && $page == 'matches') ? 'active' : '' ?>">
        <span class="icon"><i class="ph ph-heart"></i></span>
        <span>Your Matches</span>
    </a>

    <a href="<?= URLROOT ?>/userdashboard/chats" class="sidebar-item <?= (isset($page) && $page == 'chats') ? 'active' : '' ?>">
        <span class="icon"><i class="ph ph-chat-circle-dots"></i></span>
        <span>Sessions</span>
    </a>
    
    <a href="<?= URLROOT ?>/userdashboard/communities" class="sidebar-item <?= (isset($page) && $page == 'communities') ? 'active' : '' ?>">
        <span class="icon"><i class="ph ph-users-three"></i></span>
        <span>Communities</span>
    </a>
    <a href="<?= URLROOT ?>/userdashboard/quiz" class="sidebar-item <?= (isset($page) && $page == 'quiz') ? 'active' : '' ?>">
        <span class="icon"><i class="ph ph-notepad"></i></span>
        <span>Take a Quiz</span>
    </a>
    <a href="<?= URLROOT ?>/userdashboard/projects" class="sidebar-item <?= (isset($page) && $page == 'projects') ? 'active' : '' ?>">
        <span class="icon"><i class="ph ph-folder-open"></i></span>
        <span>Projects</span>
    </a>
    <a href="<?= URLROOT ?>/userdashboard/wallet" class="sidebar-item <?= (isset($page) && $page == 'wallet') ? 'active' : '' ?>">
        <span class="icon"><i class="ph ph-wallet"></i></span>
        <span>Wallet</span>
    </a>
    <a href="<?= URLROOT ?>/Feedback/index" class="sidebar-item <?= (isset($page) && $page == 'feedback') ? 'active' : '' ?>">
        <span class="icon"><i class="ph ph-chat-teardrop-text"></i></span>
        <span>My Feedback</span>
    </a>
</nav>