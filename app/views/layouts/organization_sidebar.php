<link rel="stylesheet" href="<?= URLROOT; ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT; ?>/assets/css/organizations.css">

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organization Sidebar</title>
</head>
<body>
    <div>
        <!-- Organization Sidebar -->
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
        </nav>
    </div>

    <script src="<?= URLROOT ?>/assets/js/organizations.js" defer></script>
</body>
</html>