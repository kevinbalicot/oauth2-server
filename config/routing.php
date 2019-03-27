<?php

use League\OAuth2\Server\Middleware\ResourceServerMiddleware;

use Slim\Http\Request;
use Slim\Http\Response;

// CORS middleware
$app->add(function(Request $request, Response $response, $next) {
    $response = $next($request, $response);

    $response = $response->withHeader('Access-Control-Allow-Origin', '*');
    $response = $response->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization');
    $response = $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

    return $response;
});

$app->get('/ping', function(Request $request, Response $response) use ($app) {
    return $response->withJson(['message' => 'Hello world !']);
});

$app->post('/authorize', function(Request $request, Response $response) use ($app) {

    /** @var \AuthenticationServer\Controller\AuthorizeController $controller */
    $controller = $app->getContainer()->get('authorize_controller');
    return $controller->authorize($request, $response);
});

$app->get('/validate', function(Request $request, Response $response) use ($app) {

    /** @var \AuthenticationServer\Controller\ValidateController $controller */
    $controller = $app->getContainer()->get('validate_controller');
    return $controller->validate($request, $response);
});

$app->put('/password/request', function(Request $request, Response $response) use ($app) {
    /** @var \AuthenticationServer\Controller\UserController $userController */
    $userController = $app->getContainer()->get('user_controller');

    return $userController->createPasswordSecret($request, $response);
});

$app->put('/password/reset', function(Request $request, Response $response) use ($app) {
    /** @var \AuthenticationServer\Controller\UserController $userController */
    $userController = $app->getContainer()->get('user_controller');

    return $userController->resetPassword($request, $response);
});

// Secured API
$app->group('/api', function() {

    /** @var \AuthenticationServer\Controller\UserController $userController */
    $userController = $this->getContainer()->get('user_controller');

    /** @var \AuthenticationServer\Controller\ScopeController $scopeController */
    $scopeController = $this->getContainer()->get('scope_controller');

    /** @var \AuthenticationServer\Controller\ClientController $clientController */
    $clientController = $this->getContainer()->get('client_controller');

    $this->get('/users', function(Request $request, Response $response) use ($userController) {
        return $userController->find($request, $response);
    });

    $this->get('/users/{identifier}', function(Request $request, Response $response) use ($userController) {
        return $userController->findOne($request, $response);
    });

    $this->post('/users', function(Request $request, Response $response) use ($userController) {
        return $userController->create($request, $response);
    });

    $this->post('/users/{identifier}', function(Request $request, Response $response) use ($userController) {
        return $userController->update($request, $response);
    });

    $this->post('/users/{identifier}/scope', function(Request $request, Response $response) use ($userController) {
        return $userController->addScope($request, $response);
    });

    $this->delete('/users/{identifier}/scope/{scope}', function(Request $request, Response $response) use ($userController) {
        return $userController->removeScope($request, $response);
    });

    $this->put('/users/{identifier}/enabled', function(Request $request, Response $response) use ($userController) {
        return $userController->enabled($request, $response);
    });

    $this->put('/users/{identifier}/disabled', function(Request $request, Response $response) use ($userController) {
        return $userController->disabled($request, $response);
    });

    $this->post('/scopes', function(Request $request, Response $response) use ($scopeController) {
        return $scopeController->create($request, $response);
    });

    $this->get('/scopes', function(Request $request, Response $response) use ($scopeController) {
        return $scopeController->find($request, $response);
    });

    $this->post('/clients/user', function(Request $request, Response $response) use ($clientController) {
        return $clientController->addUser($request, $response);
    });

})->add(new ResourceServerMiddleware($app->getContainer()->get('resource_server')));
