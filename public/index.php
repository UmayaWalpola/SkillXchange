<?php
// index.php (Front Controller)

// Load config (includes session_start)
require_once '../app/config/config.php';

// Load core libraries
require_once '../core/Core.php';
require_once '../core/Controller.php';
require_once '../core/Database.php';

// Optional: Autoload app libraries
spl_autoload_register(function($className) {
    $file = '../app/libraries/' . $className . '.php';
    if(file_exists($file)) require_once $file;
});

// Start the app (Core handles routing)
$core = new Core();
