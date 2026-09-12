<?php

declare(strict_types=1);

use App\Web\NotFound\NotFoundHandler;
use App\Web\Shared\LocaleMiddleware;
use Yiisoft\Csrf\CsrfTokenMiddleware;
use Yiisoft\DataResponse\Middleware\FormatDataResponse;
use Yiisoft\Definitions\DynamicReference;
use Yiisoft\Definitions\Reference;
use Yiisoft\ErrorHandler\Middleware\ErrorCatcher;
use Yiisoft\Input\Http\HydratorAttributeParametersResolver;
use Yiisoft\Input\Http\RequestInputParametersResolver;
use Yiisoft\Middleware\Dispatcher\CompositeParametersResolver;
use Yiisoft\Middleware\Dispatcher\MiddlewareDispatcher;
use Yiisoft\Middleware\Dispatcher\ParametersResolverInterface;
use Yiisoft\RequestProvider\RequestCatcherMiddleware;
use Yiisoft\Router\Middleware\Router;
use Yiisoft\Session\SessionMiddleware;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\Http\Application;

/** @var array $params */

return [
    Application::class => [
        '__construct()' => [
            'dispatcher' => DynamicReference::to([
                'class' => MiddlewareDispatcher::class,
                'withMiddlewares()' => [
                    [
                        ErrorCatcher::class,
                        SessionMiddleware::class,
                        CsrfTokenMiddleware::class,
                        LocaleMiddleware::class,
                        FormatDataResponse::class,
                        RequestCatcherMiddleware::class,
                        Router::class,
                    ],
                ],
            ]),
            'fallbackHandler' => Reference::to(NotFoundHandler::class),
        ],
    ],

    LocaleMiddleware::class => [
        '__construct()' => [
            'translator' => Reference::to(TranslatorInterface::class),
            'params' => $params,
        ],
    ],

    ParametersResolverInterface::class => [
        'class' => CompositeParametersResolver::class,
        '__construct()' => [
            Reference::to(HydratorAttributeParametersResolver::class),
            Reference::to(RequestInputParametersResolver::class),
        ],
    ],
];
