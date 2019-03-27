<?php
ini_set('display_errors','on');
error_reporting(E_ERROR);

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/config.php';

$app = new \Slim\App($config);

require __DIR__ . '/../config/dependencies.php';
require __DIR__ . '/../config/routing.php';

$app->run();
