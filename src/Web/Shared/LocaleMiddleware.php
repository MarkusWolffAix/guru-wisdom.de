<?php

declare(strict_types=1);

namespace App\Web\Shared;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Translator\TranslatorInterface;

final class LocaleMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly array $params = []
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $multilingual = $this->params['multilingual'] ?? [];
        $available = $this->params['locales']['available'] ?? ['de', 'en'];
        $default = $this->params['locales']['default'] ?? 'de';

        $locale = $default;
        $host = $request->getUri()->getHost();

        // 1. Domain-Routing (Prod: .com -> en, .de -> de)
        if (str_ends_with($host, '.com')) {
            $locale = 'en';
        } elseif (str_ends_with($host, '.de')) {
            $locale = 'de';
        }

        // 2. GET-Parameter Override für Dev und Testing
        if (!empty($multilingual['allow_query_override'])) {
            $queryParams = $request->getQueryParams();
            if (isset($queryParams['lang']) && in_array($queryParams['lang'], $available, true)) {
                $locale = (string) $queryParams['lang'];
            }
        }

        // Translator global auf die ermittelte Sprache setzen
        $this->translator->setLocale($locale);

        // Request anreichern für Actions, Layout und View-Templates
        $request = $request
            ->withAttribute('current_locale', $locale)
            ->withAttribute('enable_lang_support_button', (bool) ($multilingual['enable_lang_support_button'] ?? false));

        return $handler->handle($request);
    }
}