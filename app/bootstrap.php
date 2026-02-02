<?php
/**
 * SkillXchange Bootstrap File
 * This file initializes the application and loads all necessary components
 * Used by: batch scripts, cron jobs, and other standalone scripts
 */

// Load configuration
require_once dirname(__FILE__) . '/config/config.php';

// Autoload core libraries (Database, Controller, etc.)
spl_autoload_register(function($className) {
    $paths = [
        dirname(__FILE__) . '/libraries/' . $className . '.php',
        dirname(__FILE__) . '/models/' . $className . '.php',
        dirname(__FILE__) . '/controllers/' . $className . '.php',
        dirname(__FILE__) . '/helpers/' . $className . '.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Helper function to manually load a library if needed
function loadLibrary($libraryName) {
    $path = dirname(__FILE__) . '/libraries/' . $libraryName . '.php';
    if (file_exists($path)) {
        require_once $path;
        return true;
    }
    return false;
}

// Helper function to manually load a model if needed
function loadModel($modelName) {
    $path = dirname(__FILE__) . '/models/' . $modelName . '.php';
    if (file_exists($path)) {
        require_once $path;
        return true;
    }
    return false;
}