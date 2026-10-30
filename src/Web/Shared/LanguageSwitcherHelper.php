<?php

declare(strict_types=1);

namespace App\Web\Shared;

use Psr\Http\Message\ServerRequestInterface;

final class LanguageSwitcherHelper
{
    public static function getTargetUrl(ServerRequestInterface $request, array $params): ?string
    {
        $multilingual = $params['multilingual'] ?? [];

        // Check if the language switch button is enabled
        if (empty($multilingual['enable_lang_support_button'])) {
            return null;
        }

        $defaultLocale = $multilingual['default_locale'] ?? 'de';
        $currentLocale = (string) ($request->getAttribute('current_locale', $defaultLocale));
        $targetLocale = ($currentLocale === 'de') ? 'en' : 'de';
        $uri = $request->getUri();

        // Strip optional port from host header
        $rawHost = $uri->getHost();
        $host = strtok($rawHost, ':');

        // Mode 1: Development/Testing query parameter override
        if (!empty($multilingual['allow_query_override'])) {
            $queryParams = $request->getQueryParams();
            $queryParams['lang'] = $targetLocale;
            return (string) $uri->withQuery(http_build_query($queryParams));
        }

        // Mode 2: Strict environment-pair domain switching
        $switchMap = $multilingual['domain_switch_map'] ?? [];
        $targetHost = $switchMap[$host] ?? $host;

        return (string) $uri->withHost($targetHost);
    }
}
