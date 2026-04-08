<aside class="sidebar">
    <a href="<?= URLROOT ?>/manager" class="sidebar-item <?= ($data['page'] ?? '') == 'dashboard' ? 'active' : '' ?>">
        <span class="icon"><i class="ph ph-chart-bar"></i></span>
        <span>Dashboard</span>
    </a>
    
    <a href="<?= URLROOT ?>/manager/organizations" class="sidebar-item <?= ($data['page'] ?? '') == 'organizations' ? 'active' : '' ?>">
        <span class="icon"><i class="ph ph-buildings"></i></span>
        <span>Organizations</span>
    </a>
    
    <a href="<?= URLROOT ?>/manager/users" class="sidebar-item <?= ($data['page'] ?? '') == 'users' ? 'active' : '' ?>">
        <span class="icon"><i class="ph ph-users"></i></span>
        <span>User Management</span>
    </a>
    
    <a href="<?= URLROOT ?>/manager/announcements" class="sidebar-item <?= ($data['page'] ?? '') == 'announcements' ? 'active' : '' ?>">
        <span class="icon"><i class="ph ph-megaphone"></i></span>
        <span>Announcements</span>
    </a>
    
    <a href="<?= URLROOT ?>/manager/feedback" class="sidebar-item <?= ($data['page'] ?? '') == 'feedback' ? 'active' : '' ?>">
        <span class="icon"><i class="ph ph-chat-circle-dots"></i></span>
        <span>Feedback</span>
    </a>

    <a href="<?= URLROOT ?>/manager/insights" class="sidebar-item <?= ($data['page'] ?? '') == 'insights' ? 'active' : '' ?>">
        <span class="icon"><i class="ph ph-chart-pie"></i></span>
        <span>User Insights</span>
    </a>

    <a href="<?= URLROOT ?>/FeedbackReport/index" class="sidebar-item <?= (strpos($_SERVER['REQUEST_URI'], '/FeedbackReport') !== false) ? 'active' : '' ?>">
        <span class="icon"><i class="ph ph-flag"></i></span>
        <span>Reported Feedback</span>
    </a>
</aside>