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
        'enable_lang_support_button' => false, // default deactivated, can be activated in the future
        'allow_query_override' => false,      // only for development and testing purposes
        'domains' => [
            'de' => 'guru-wisdom.de',
            'en' => 'guru-wisdom.com',
        ],
    ],
   
];
