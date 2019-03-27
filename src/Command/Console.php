<?php

require __DIR__ . '/../../vendor/autoload.php';

use Symfony\Component\Console\Application;
use \AuthenticationServer\Command as Command;

$config = require __DIR__ . '/../../config/config.php';

$app = new Application();
$container = new \Slim\Container($config);

require __DIR__ . '/../../config/dependencies.php';

$app->add(new Command\User\CreateUser($container));
$app->add(new Command\User\InfoUser($container));
$app->add(new Command\User\AddScopeToUser($container));
$app->add(new Command\User\ListUsers($container));
$app->add(new Command\User\DeleteUser($container));
$app->add(new Command\Client\CreateClient($container));
$app->add(new Command\Client\AddUserToClient($container));
$app->add(new Command\Client\ListClients($container));
$app->add(new Command\Client\InfoClient($container));
$app->add(new Command\Client\DeleteClient($container));
$app->add(new Command\Client\AddScopeToClient($container));
$app->add(new Command\Scope\CreateScope($container));
$app->add(new Command\Scope\ListScopes($container));
$app->add(new Command\Scope\InfoScope($container));
$app->add(new Command\Scope\DeleteScope($container));
$app->add(new Command\Bootstrap($container));
$app->add(new Command\State($container));
$app->add(new Command\Home($container));
$app->add(new Command\Authorize($container));
$app->add(new Command\Cleanup($container));

$app->run();
