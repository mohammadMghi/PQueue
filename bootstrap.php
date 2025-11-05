<?php

/**
 * Bootstrap file - loads autoloader and initializes application
 */
require __DIR__ . '/vendor/autoload.php';

use PQueue\Application;

// Initialize application
$app = new Application();

return $app;

