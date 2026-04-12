<aside class="sidebar">
    <a href="<?= URLROOT ?>/managerdashboard" class="sidebar-item <?= ($data['page'] ?? '') == 'dashboard' ? 'active' : '' ?>">
        <span class="icon"><i class="ph ph-chart-bar"></i></span>
        <span>Dashboard</span>
    </a>
    
    <a href="<?= URLROOT ?>/managerdashboard/organizations" class="sidebar-item <?= ($data['page'] ?? '') == 'organizations' ? 'active' : '' ?>">
        <span class="icon"><i class="ph ph-buildings"></i></span>
        <span>Organizations</span>
    </a>
    
    <a href="<?= URLROOT ?>/managerdashboard/users" class="sidebar-item <?= ($data['page'] ?? '') == 'users' ? 'active' : '' ?>">
        <span class="icon"><i class="ph ph-users"></i></span>
        <span>User Management</span>
    </a>
    
    <a href="<?= URLROOT ?>/managerdashboard/announcements" class="sidebar-item <?= ($data['page'] ?? '') == 'announcements' ? 'active' : '' ?>">
        <span class="icon"><i class="ph ph-megaphone"></i></span>
        <span>Announcements</span>
    </a>
    
    <a href="<?= URLROOT ?>/managerdashboard/feedback" class="sidebar-item <?= ($data['page'] ?? '') == 'feedback' ? 'active' : '' ?>">
        <span class="icon"><i class="ph ph-chat-circle-dots"></i></span>
        <span>Feedback</span>
    </a>

    <a href="<?= URLROOT ?>/FeedbackReport/index" class="sidebar-item <?= (strpos($_SERVER['REQUEST_URI'], '/FeedbackReport') !== false) ? 'active' : '' ?>">
        <span class="icon"><i class="ph ph-flag"></i></span>
        <span>Reported Feedback</span>
    </a>
</aside>