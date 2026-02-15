<?php
/**
 * SkillXchange Bootstrap File
 * This file initializes the application and loads all necessary components
 * Used by: batch scripts, cron jobs, and other standalone scripts
 */

// Load configuration
require_once dirname(__FILE__) . '/config/config.php';

//Load Database class from CORE folder (not libraries!)
require_once dirname(dirname(__FILE__)) . '/core/Database.php';

// Autoloader for other classes
spl_autoload_register(function($className) {
    $paths = [
        dirname(dirname(__FILE__)) . '/core/' . $className . '.php',
        dirname(__FILE__) . '/models/' . $className . '.php',
        dirname(__FILE__) . '/controllers/' . $className . '.php',
    ];    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

