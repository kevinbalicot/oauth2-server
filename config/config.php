<?php
return [
    'settings' => [
        'displayErrorDetails' => true,
        'RefreshTokenTTL' => 'P1M',
        'AccessTokenTTL' => 'PT1H',

        'logger' => [
            'name' => 'auth-server',
            'level' => Monolog\Logger::INFO,
            'path' => __DIR__ . '/../logs/app.log',
        ],

        'keys' => [
            'public' => __DIR__ . '/../resources/keys/public.key',
            'private' => __DIR__ . '/../resources/keys/private.key',
        ],

        'mailer' => [
            'uri' => 'http://mailer.example.com/mail' // HTTP service to send mail (need refacto)
        ]
    ],
    
    'doctrine' => [
        'meta' => [
            'entity_path' => [
                __DIR__ . '/../resources/schemas'
            ],
            'auto_generate_proxies' => true,
            'proxy_dir' =>  __DIR__.'/../cache/proxies',
            'cache' => null,
        ],
        'connection' => [
            'driver'   => 'pdo_mysql',
            'host'     => 'mysql', // Docker container host for mysql
            'dbname'   => 'authenticate',
            'user'     => 'root',
            'password' => 'root',
        ]
    ]
];
