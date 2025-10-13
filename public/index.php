<?php
// index.php (Front Controller)

// Start session at the very beginning
session_start();

// Load config
require_once '../app/config/config.php';

// Load core libraries
require_once '../core/Core.php';
require_once '../core/Controller.php';
require_once '../core/Database.php';

// Autoload core libraries
spl_autoload_register(function($className) {
    $file = '../app/libraries/' . $className . '.php';
    if(file_exists($file)) {
        require_once $file;
    }
});

// Start the app (Core handles routing)
$core = new Core();