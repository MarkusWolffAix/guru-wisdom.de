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
        private readonly array $params
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $multilingual = $this->params['multilingual'] ?? [];
        $defaultLocale = $multilingual['default_locale'] ?? 'de';
        $available = $multilingual['locales']['available'] ?? ['de', 'en'];
        $domainToLocale = $multilingual['domain_to_locale'] ?? [];

        // Strip optional port from host header (e.g. localhost:8080 -> localhost)
        $rawHost = $request->getUri()->getHost();
        $host = strtok($rawHost, ':');

        // 1. Exact domain resolution with fallback to default locale
        $locale = $domainToLocale[$host] ?? $defaultLocale;

        // 2. Query parameter override (strictly when explicitly enabled)
        if (!empty($multilingual['allow_query_override'])) {
            $queryParams = $request->getQueryParams();
            if (isset($queryParams['lang']) && in_array($queryParams['lang'], $available, true)) {
                $locale = (string) $queryParams['lang'];
            }
        }

        // Apply locale globally to the translator
        $this->translator->setLocale($locale);

        // Enrich the request attributes for actions, layout, and view templates
        $request = $request
            ->withAttribute('current_locale', $locale)
            ->withAttribute('enable_lang_support_button', (bool) ($multilingual['enable_lang_support_button'] ?? false));

        return $handler->handle($request);
    }
}
