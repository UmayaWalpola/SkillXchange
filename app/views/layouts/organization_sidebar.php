<nav class="sidebar">
            <a href="<?= URLROOT ?>/organization/profile" class="sidebar-item <?= (strpos($_SERVER['REQUEST_URI'], '/profile') !== false) ? 'active' : '' ?>">
                <span>Profile</span>
            </a>
            
            <a href="<?= URLROOT ?>/organization/projects" class="sidebar-item <?= (strpos($_SERVER['REQUEST_URI'], '/projects') !== false) ? 'active' : '' ?>">
                <span>Projects</span>
            </a>
            
            <a href="<?= URLROOT ?>/organization/applications" class="sidebar-item <?= (strpos($_SERVER['REQUEST_URI'], '/applications') !== false) ? 'active' : '' ?>">
                <span>Applications</span>
            </a>
            
            <a href="<?= URLROOT ?>/organization/chats" class="sidebar-item <?= (strpos($_SERVER['REQUEST_URI'], '/chats') !== false) ? 'active' : '' ?>">
                <span>Chats</span>
            </a>

            <a href="<?= URLROOT ?>/organization/wallet" class="sidebar-item <?= (strpos($_SERVER['REQUEST_URI'], '/wallet') !== false) ? 'active' : '' ?>">
                <span>Wallet</span>
            </a>
</nav>