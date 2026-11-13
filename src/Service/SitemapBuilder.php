<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Builds sitemap.xml and robots.txt for one domain. Pure functions, no I/O.
 *
 * Every page exists on both domains with the same path (see LanguageSwitcherHelper), so each URL gets
 * hreflang alternates pointing to the partner domain (multilingual.domain_switch_map). Drafts are never listed.
 */
final class SitemapBuilder
{
    /** Pages that exist in both languages. */
    public const STATIC_PATHS = ['/', '/contact', '/imprint', '/privacy'];

    /**
     * Canonical base URL of a host: always https for public domains, "www." is dropped
     * (www redirects to the apex). Local hosts keep their scheme and port.
     */
    public static function baseUrl(string $host, string $scheme = 'https', ?int $port = null): string
    {
        $host = strtolower((string) strtok($host, ':'));

        if (!self::isLocalHost($host)) {
            return 'https://' . (preg_replace('/^www\./', '', $host) ?? $host);
        }

        $scheme = $scheme === 'https' ? 'https' : 'http';
        $portPart = ($port !== null && !in_array($port, [80, 443], true)) ? ':' . $port : '';

        return $scheme . '://' . $host . $portPart;
    }

    /**
     * Canonical URL of a page: base URL of the host plus the path, without query string and fragment.
     * Used for the canonical link, og:url and JSON-LD, so "?lang=en" or tracking parameters never end up
     * there and the scheme does not depend on what the proxy passes to the app.
     */
    public static function canonicalUrl(string $host, string $path, string $scheme = 'https', ?int $port = null): string
    {
        return self::baseUrl($host, $scheme, $port) . '/' . ltrim($path, '/');
    }

    /**
     * Base URL of the partner domain (de <-> en), or null when the host has no partner.
     *
     * @param array<string, string> $switchMap multilingual.domain_switch_map
     */
    public static function partnerBaseUrl(array $switchMap, string $host, string $scheme = 'https', ?int $port = null): ?string
    {
        $host = strtolower((string) strtok($host, ':'));
        $target = $switchMap[$host] ?? $switchMap['www.' . $host] ?? null;

        return $target === null ? null : self::baseUrl($target, $scheme, $port);
    }

    /** Test and local hosts must not be indexed. */
    public static function crawlingAllowed(string $host): bool
    {
        $host = strtolower((string) strtok($host, ':'));

        return !self::isLocalHost($host) && !str_starts_with($host, 'test.');
    }

    public static function robots(string $baseUrl, bool $allowCrawling): string
    {
        if (!$allowCrawling) {
            return "User-agent: *\nDisallow: /\n";
        }

        return "User-agent: *\nDisallow:\n\nSitemap: " . $baseUrl . "/sitemap.xml\n";
    }

    /**
     * @param array<int, array<string, mixed>> $wisdoms        Wisdoms of this domain's language (slug, date, draft).
     * @param array<int, array<string, mixed>> $partnerWisdoms Wisdoms of the partner language; only slugs that exist there get an alternate.
     */
    public static function sitemap(
        array $wisdoms,
        string $baseUrl,
        string $locale,
        ?string $partnerBaseUrl = null,
        array $partnerWisdoms = [],
    ): string {
        $published = array_values(array_filter($wisdoms, static fn (array $w): bool => empty($w['draft'])));
        $partnerSlugs = array_flip(array_column(
            array_filter($partnerWisdoms, static fn (array $w): bool => empty($w['draft'])),
            'slug',
        ));

        $latest = null;
        foreach ($published as $wisdom) {
            $date = self::date($wisdom['date'] ?? null);
            if ($date !== null && ($latest === null || $date > $latest)) {
                $latest = $date;
            }
        }

        $entries = [];
        foreach (self::STATIC_PATHS as $path) {
            $entries[] = [
                'path' => $path,
                'lastmod' => $path === '/' ? $latest : null,
                'alternate' => $partnerBaseUrl !== null,
            ];
        }
        foreach ($published as $wisdom) {
            $slug = (string) $wisdom['slug'];
            $entries[] = [
                'path' => '/' . $slug,
                'lastmod' => self::date($wisdom['date'] ?? null),
                'alternate' => $partnerBaseUrl !== null && isset($partnerSlugs[$slug]),
            ];
        }

        $otherLocale = $locale === 'de' ? 'en' : 'de';
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

        foreach ($entries as $entry) {
            $path = $entry['path'] === '/' ? '' : $entry['path'];
            $self = $baseUrl . ($path === '' ? '/' : $path);

            $xml .= "  <url>\n    <loc>" . self::esc($self) . "</loc>\n";
            if ($entry['lastmod'] !== null) {
                $xml .= '    <lastmod>' . $entry['lastmod'] . "</lastmod>\n";
            }
            if ($entry['alternate']) {
                $other = $partnerBaseUrl . ($path === '' ? '/' : $path);
                $de = $locale === 'de' ? $self : $other;
                $xml .= self::link($locale, $self)
                    . self::link($otherLocale, $other)
                    . self::link('x-default', $de);
            }
            $xml .= "  </url>\n";
        }

        return $xml . "</urlset>\n";
    }

    private static function link(string $hreflang, string $href): string
    {
        return '    <xhtml:link rel="alternate" hreflang="' . $hreflang . '" href="' . self::esc($href) . "\"/>\n";
    }

    /** Normalises "2026-11-7" to "2026-11-07"; null when it is not a date. */
    private static function date(mixed $value): ?string
    {
        $timestamp = strtotime((string) $value);

        return $timestamp === false ? null : date('Y-m-d', $timestamp);
    }

    private static function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private static function isLocalHost(string $host): bool
    {
        return $host === 'localhost' || str_ends_with($host, '.localhost');
    }
}
