<?php

/**
 * Laravel Public Entry Point
 */

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the auto loader classes, so the autoloader can find the app's classes
require_once __DIR__ . '/../vendor/autoload.php';
