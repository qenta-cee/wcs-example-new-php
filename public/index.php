<?php
$baseDir = __DIR__ . '/..';
require $baseDir . '/vendor/autoload.php';

$settings = require $baseDir . '/config/settings.php';
$app = new \Slim\App($settings);

// set up dependencies
require $baseDir . '/config/dependencies.php';

// register middleware
require $baseDir . '/config/middleware.php';

// register routes
require $baseDir . '/config/routes.php';

$app->run();