<?php

declare(strict_types=1);

return [
    'app' => [
        'name' => 'GURU Wisdom',
        'charset' => 'UTF-8',
        'language' => 'de-DE',
        'timezone' => 'Europe/Berlin',
    ],
    
    'yiisoft/aliases' => [
        'aliases' => [
            '@root' => dirname(__DIR__),
            '@assets' => '@public/assets',
            '@assetsUrl' => '/assets',
            '@runtime' => '@root/runtime',
            '@bower' => '@root/vendor/bower-asset',
            '@npm'   => '@root/vendor/npm-asset',
            // Dein Session-Pfad aus Yii2:
            '@sessionPath' => '@runtime/sessions', 
        ],
    ],

    'yiisoft/cookies' => [
        // Dein alter cookieValidationKey
        'secretKey' => 'UJxxD25WdCiy4zAE9MolOQpdeZRwgkbH', 
    ],

    'yiisoft/router' => [
    	'routes' => require __DIR__ . '/routes.php',
    ],    
    // ... andere Parameter ...
];
