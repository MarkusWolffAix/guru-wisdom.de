<?php

declare(strict_types=1);

namespace App\Web\Shared;

use Psr\Http\Message\ServerRequestInterface;

final class LanguageSwitcherHelper
{
    public static function getTargetUrl(ServerRequestInterface $request, array $params): ?string
    {
        $multilingual = $params['multilingual'] ?? [];

        // Prüfen, ob der Button aktiviert ist
        if (empty($multilingual['enable_lang_support_button'])) {
            return null;
        }

        $currentLocale = (string) ($request->getAttribute('current_locale', 'de'));
        $targetLocale = ($currentLocale === 'de') ? 'en' : 'de';
        $uri = $request->getUri();
        $host = $uri->getHost();

        // Verhalten 1: Testing (test.guru-wisdom.de) & Dev (localhost)
        if (!empty($multilingual['allow_query_override'])) {
            $queryParams = $request->getQueryParams();
            $queryParams['lang'] = $targetLocale;
            return (string) $uri->withQuery(http_build_query($queryParams));
        }

        // Verhalten 2: Prod (Domain-Wechsel guru-wisdom.de <-> guru-wisdom.com)
        $targetHost = $multilingual['domains'][$targetLocale] ?? $host;
        return (string) $uri->withHost($targetHost);
    }
}