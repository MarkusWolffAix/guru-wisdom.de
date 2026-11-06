<?php

declare(strict_types=1);

use App\Shared\ApplicationParams;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Assets\AssetManager;
use Yiisoft\Definitions\Reference;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\CsrfViewInjection;
use Yiisoft\Translator\TranslatorInterface;

return [
    'application' => require __DIR__ . '/application.php',

    'yiisoft/aliases' => [
        'aliases' => require __DIR__ . '/aliases.php',
    ],

    'yiisoft/view' => [
        'basePath' => null,
        'parameters' => [
            'assetManager' => Reference::to(AssetManager::class),
            'applicationParams' => Reference::to(ApplicationParams::class),
            'aliases' => Reference::to(Aliases::class),
            'urlGenerator' => Reference::to(UrlGeneratorInterface::class),
            'currentRoute' => Reference::to(CurrentRoute::class),
            'translator' => Reference::to(TranslatorInterface::class),
        ],
    ],

    'yiisoft/yii-view-renderer' => [
        'viewPath' => null,
        'layout' => '@src/Web/Shared/Layout/Main/layout.php',
        'injections' => [
            Reference::to(CsrfViewInjection::class),
        ],
    ],

    'yiisoft/translator' => [
        'locale' => 'de',
        'fallbackLocale' => 'de',
        'defaultCategory' => 'app', 
    ],

    'locales' => [
        'available' => ['de', 'en'],
        'default' => 'de',
    ],

    
    'multilingual' => [
        'default_locale' => 'de',
        'locales' => [
            'available' => ['de', 'en'],
        ],
        'enable_lang_support_button' => false,
        'allow_query_override' => false,

        // Hostname to locale mapping (Dev, Staging, Production)
        'domain_to_locale' => [
            // Local Development
            'localhost'            => 'de',
            'de.localhost'         => 'de',
            'en.localhost'         => 'en',

            // Staging / Testing
            'test.guru-wisdom.de'  => 'de',
            'test.guru-wisdom.com' => 'en',

            // Production
            'guru-wisdom.de'       => 'de',
            'www.guru-wisdom.de'   => 'de',
            'guru-wisdom.com'      => 'en',
            'www.guru-wisdom.com'  => 'en',
        ],

        // Target switch host for each environment pair
        'domain_switch_map' => [
            // Local Development
            'localhost'            => 'en.localhost',
            'de.localhost'         => 'en.localhost',
            'en.localhost'         => 'localhost',

            // Staging / Testing
            'test.guru-wisdom.de'  => 'test.guru-wisdom.com',
            'test.guru-wisdom.com' => 'test.guru-wisdom.de',

            // Production
            'guru-wisdom.de'       => 'guru-wisdom.com',
            'www.guru-wisdom.de'   => 'guru-wisdom.com',
            'guru-wisdom.com'      => 'guru-wisdom.de',
            'www.guru-wisdom.com'  => 'guru-wisdom.de',
        ],
    ],

   
];
