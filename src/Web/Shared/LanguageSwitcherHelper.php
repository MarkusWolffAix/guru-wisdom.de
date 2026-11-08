<?php

declare(strict_types=1);

namespace App\Web\Shared;

/**
 * Builds the data for the DE | EN switch.
 *
 * Every valid page has the same path on both domains (/, /contact, /imprint, /privacy, /{wisdom-id}),
 * so the target is the current path on the partner domain (see multilingual.domain_switch_map).
 * A wisdom that does not exist in the other language, and any unknown path, leads to the start page
 * of the other language instead of a 404.
 */
final class LanguageSwitcherHelper
{
    /** Paths that exist in both languages. */
    private const STATIC_PATHS = ['/', '/contact', '/imprint', '/privacy'];

    private const LANGUAGE_NAMES = ['de' => 'Deutsch', 'en' => 'English'];

    /**
     * @param array<string, mixed> $multilingual The 'multilingual' params.
     * @param string $host        Host of the current request (port is ignored).
     * @param string $path        Path of the current request, e.g. "/Urd".
     * @param string $query       Raw query string of the current request.
     * @param string $scheme      Scheme of the current request (used only for local hosts).
     * @param string $wisdomsDir  Directory with the German wisdoms; English ones are in "<dir>/en".
     *
     * @return array{
     *     current: string, target: string, currentLabel: string, targetLabel: string,
     *     targetName: string, url: string, alternate: ?array{self: string, other: string}
     * }|null null when the switch is disabled or the host is unknown.
     */
    public static function build(
        array $multilingual,
        string $host,
        string $path,
        string $query,
        string $scheme,
        string $wisdomsDir,
        string $currentLocale,
    ): ?array {
        if (empty($multilingual['enable_lang_support_button'])) {
            return null;
        }

        $host = strtolower((string) strtok($host, ':'));
        $targetHost = $multilingual['domain_switch_map'][$host] ?? null;
        if ($targetHost === null) {
            return null;
        }

        $targetLocale = $currentLocale === 'de' ? 'en' : 'de';
        $path = $path === '' ? '/' : $path;

        $existsInTarget = self::existsInLocale($path, $targetLocale, $wisdomsDir);
        $targetPath = $existsInTarget ? $path : '/';
        // Only the query of a page that exists in both languages is meaningful for the other domain.
        $targetQuery = ($existsInTarget && $query !== '') ? '?' . $query : '';

        $url = self::origin($targetHost, $scheme) . $targetPath . $targetQuery;

        // hreflang pairs only for pages that really exist in both languages
        $alternate = null;
        if ($existsInTarget && self::existsInLocale($path, $currentLocale, $wisdomsDir)) {
            $alternate = [
                'self' => self::origin($host, $scheme) . $path,
                'other' => self::origin($targetHost, $scheme) . $path,
            ];
        }

        return [
            'current' => $currentLocale,
            'target' => $targetLocale,
            'currentLabel' => strtoupper($currentLocale),
            'targetLabel' => strtoupper($targetLocale),
            'targetName' => self::LANGUAGE_NAMES[$targetLocale],
            'url' => $url,
            'alternate' => $alternate,
        ];
    }

    private static function existsInLocale(string $path, string $locale, string $wisdomsDir): bool
    {
        if (in_array($path, self::STATIC_PATHS, true)) {
            return true;
        }
        if (preg_match('#^/([A-Za-z0-9_-]+)$#', $path, $m) !== 1) {
            return false;
        }

        $dir = rtrim($wisdomsDir, '/') . ($locale === 'en' ? '/en' : '');

        return is_file($dir . '/' . $m[1] . '.md');
    }

    private static function origin(string $host, string $scheme): string
    {
        // Behind the proxy the request scheme can be http; the public domains are https only.
        $local = $host === 'localhost' || str_ends_with($host, '.localhost');

        return ($local ? $scheme : 'https') . '://' . $host;
    }
}
