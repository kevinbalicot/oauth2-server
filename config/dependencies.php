<?php

$container = isset($container) ? $container : $app->getContainer();

$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        return $c['response']
            ->withStatus(404)
            ->withJson(['error' => 'not_found', 'message' => 'Page not found']);
    };
};

$container['em'] = function ($c) {
    $settings = $c->get('doctrine');
    $config = Doctrine\ORM\Tools\Setup::createYAMLMetadataConfiguration(
        $settings['meta']['entity_path'],
        $settings['meta']['auto_generate_proxies'],
        $settings['meta']['proxy_dir'],
        $settings['meta']['cache']
    );

    return Doctrine\ORM\EntityManager::create($settings['connection'], $config);
};

$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));

    return $logger;
};

$container['http_client'] = function() {
    return new \AuthenticationServer\Service\HTTPClient();
};

$container['mailer'] = function($c) {
    $setting = $c->get('settings')['mailer'];

    return new \AuthenticationServer\Service\Mailer($c->get('http_client'), $setting['uri']);
};

$container['client_repository'] = function($c) {
    $className = AuthenticationServer\Entity\Client::class;
    return new AuthenticationServer\Repository\ClientRepository(
        $c->get('em'),
        new Doctrine\ORM\Mapping\ClassMetadata($className)
    );
};

$container['user_repository'] = function($c) {
    $className = AuthenticationServer\Entity\User::class;
    return new AuthenticationServer\Repository\UserRepository(
        $c->get('em'),
        new Doctrine\ORM\Mapping\ClassMetadata($className),
        new AuthenticationServer\Hasher\PHPHasher()
    );
};

$container['scope_repository'] = function($c) {
    $className = AuthenticationServer\Entity\Scope::class;
    return new AuthenticationServer\Repository\ScopeRepository(
        $c->get('em'),
        new Doctrine\ORM\Mapping\ClassMetadata($className),
        $c->get('user_repository')
    );
};

$container['access_token_repository'] = function($c) {
    $className = AuthenticationServer\Entity\AccessToken::class;
    return new AuthenticationServer\Repository\AccessTokenRepository(
        $c->get('em'),
        new Doctrine\ORM\Mapping\ClassMetadata($className),
        $c->get('user_repository')
    );
};

$container['refresh_token_repository'] = function($c) {
    $className = AuthenticationServer\Entity\RefreshToken::class;
    return new AuthenticationServer\Repository\RefreshTokenRepository(
        $c->get('em'),
        new Doctrine\ORM\Mapping\ClassMetadata($className)
    );
};

$container['authentication_server'] = function($c) {

    $server = new League\OAuth2\Server\AuthorizationServer(
        $c->get('client_repository'),
        $c->get('access_token_repository'),
        $c->get('scope_repository'),
        $c->get('settings')['keys']['private'],
        $c->get('settings')['keys']['public']
    );

    $passwordGrant = new League\OAuth2\Server\Grant\PasswordGrant(
        $c->get('user_repository'),
        $c->get('refresh_token_repository')
    );

    $refreshTokenGrant = new League\OAuth2\Server\Grant\RefreshTokenGrant(
        $c->get('refresh_token_repository')
    );

    $googleGrant = new \AuthenticationServer\Grant\GoogleGrant(
        $c->get('user_repository'),
        $c->get('client_repository'),
        $c->get('refresh_token_repository'),
        $c->get('mailer'),
        $c->get('http_client')
    );

    $clientCredentialGrants = new League\OAuth2\Server\Grant\ClientCredentialsGrant();

    $passwordGrant->setRefreshTokenTTL(new \DateInterval($c->get('settings')['RefreshTokenTTL']));
    $refreshTokenGrant->setRefreshTokenTTL(new \DateInterval($c->get('settings')['RefreshTokenTTL']));

    $server->enableGrantType(
        $passwordGrant,
        new \DateInterval($c->get('settings')['AccessTokenTTL'])
    );

    $server->enableGrantType(
        $refreshTokenGrant,
        new \DateInterval($c->get('settings')['AccessTokenTTL'])
    );

    $server->enableGrantType(
        $clientCredentialGrants,
        new \DateInterval($c->get('settings')['AccessTokenTTL'])
    );

    $server->enableGrantType(
        $googleGrant,
        new \DateInterval($c->get('settings')['AccessTokenTTL'])
    );

    return $server;
};

$container['resource_server'] = function($c) {

    $server = new League\OAuth2\Server\ResourceServer(
        $c->get('access_token_repository'),
        $c->get('settings')['keys']['public']
    );

    return $server;
};

$container['security'] = function() {
    return new AuthenticationServer\Service\Security();
};

$container['authorize_controller'] = function($c) {
    return new AuthenticationServer\Controller\AuthorizeController($c->get('authentication_server'));
};

$container['validate_controller'] = function($c) {
    return new AuthenticationServer\Controller\ValidateController($c->get('resource_server'));
};

$container['user_controller'] = function($c) {
    return new AuthenticationServer\Controller\UserController(
        $c->get('user_repository'),
        $c->get('scope_repository'),
        $c->get('client_repository'),
        $c->get('security'),
        $c->get('mailer')
    );
};

$container['client_controller'] = function($c) {
    return new AuthenticationServer\Controller\ClientController(
        $c->get('user_repository'),
        $c->get('scope_repository'),
        $c->get('client_repository'),
        $c->get('security')
    );
};

$container['scope_controller'] = function($c) {
    return new AuthenticationServer\Controller\ScopeController($c->get('scope_repository'), $c->get('security'));
};
