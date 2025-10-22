<aside class="sidebar">
    <a href="<?= URLROOT ?>/managerdashboard" class="sidebar-item <?= ($data['page'] ?? '') == 'dashboard' ? 'active' : '' ?>">
        <span class="icon">📊</span>
        <span>Dashboard</span>
    </a>
    
    <a href="<?= URLROOT ?>/managerdashboard/organizations" class="sidebar-item <?= ($data['page'] ?? '') == 'organizations' ? 'active' : '' ?>">
        <span class="icon">🏢</span>
        <span>Organizations</span>
    </a>
    
    <a href="<?= URLROOT ?>/managerdashboard/users" class="sidebar-item <?= ($data['page'] ?? '') == 'users' ? 'active' : '' ?>">
        <span class="icon">👥</span>
        <span>User Management</span>
    </a>
    
    <a href="<?= URLROOT ?>/managerdashboard/announcements" class="sidebar-item <?= ($data['page'] ?? '') == 'announcements' ? 'active' : '' ?>">
        <span class="icon">📢</span>
        <span>Announcements</span>
    </a>
    
    <a href="<?= URLROOT ?>/managerdashboard/feedback" class="sidebar-item <?= ($data['page'] ?? '') == 'feedback' ? 'active' : '' ?>">
        <span class="icon">💬</span>
        <span>Feedback</span>
    </a>
</aside>