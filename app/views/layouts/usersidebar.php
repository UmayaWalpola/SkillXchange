<!-- Sidebar Dashboard -->
<nav class="sidebar">
    <a href="<?= URLROOT ?>/userdashboard" class="sidebar-item <?= (isset($page) && $page == 'profile') ? 'active' : '' ?>">
        <span class="icon"></span>
        <span>Profile</span>
    </a>
    <a href="<?= URLROOT ?>/userdashboard/notifications" class="sidebar-item <?= (isset($page) && $page == 'notifications') ? 'active' : '' ?>">
        <span class="icon"></span>
        <span>Notifications</span>
    </a>
    <a href="<?= URLROOT ?>/userdashboard/chats" class="sidebar-item <?= (isset($page) && $page == 'chats') ? 'active' : '' ?>">
        <span class="icon"></span>
        <span>Chats</span>
    </a>
    <a href="<?= URLROOT ?>/userdashboard/matches" class="sidebar-item <?= (isset($page) && $page == 'matches') ? 'active' : '' ?>">
        <span class="icon"></span>
        <span>Your Matches</span>
    </a>
    <a href="<?= URLROOT ?>/userdashboard/communities" class="sidebar-item <?= (isset($page) && $page == 'communities') ? 'active' : '' ?>">
        <span class="icon"></span>
        <span>Communities</span>
    </a>
    <a href="<?= URLROOT ?>/userdashboard/quiz" class="sidebar-item <?= (isset($page) && $page == 'quiz') ? 'active' : '' ?>">
        <span class="icon"></span>
        <span>Take a Quiz</span>
    </a>
    <a href="<?= URLROOT ?>/userdashboard/projects" class="sidebar-item <?= (isset($page) && $page == 'projects') ? 'active' : '' ?>">
        <span class="icon"></span>
        <span>Projects</span>
    </a>
    <a href="<?= URLROOT ?>/userdashboard/wallet" class="sidebar-item <?= (isset($page) && $page == 'wallet') ? 'active' : '' ?>">
        <span class="icon"></span>
        <span>Wallet</span>
    </a>
</nav>