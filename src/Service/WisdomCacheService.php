<?php

declare(strict_types=1);

namespace App\Service;

use Yiisoft\Cache\CacheInterface;
use App\Service\GuruWisdomService;

class WisdomCacheService
{
    /** Time zone in which publication dates are read. */
    public const TIMEZONE = 'Europe/Berlin';

    public function __construct(
        private CacheInterface $cache,
        private GuruWisdomService $guruWisdom
    ) {
    }

    /**
     * Liefert alle sortierten Weisheiten für die gewünschte Sprache.
     * Nutzt für jede Sprache einen separaten Cache-Eintrag.
     */
    public function getSortedWisdoms(string $locale = 'de'): array
    {
        // In dev the drafts are part of the index (the stamp tells the two apart, see getContentStamp()).
        $draftsEnabled = $this->guruWisdom->areDraftsEnabled();
        // The content stamp is part of the key: a new, edited or removed file gives a new key,
        // so a stale index is never served, even without clearing the cache. The old entries expire.
        $cacheKey = self::cacheKey($locale, $this->guruWisdom->getContentStamp($locale));
        $ttl = $draftsEnabled ? 3600 : 86400;

        $wisdoms = $this->cache->getOrSet($cacheKey, function () use ($locale) {
            $wisdoms = [];

            // 1. Alle IDs der Sprache (veröffentlichte Weisheiten, in dev zusätzlich die Entwürfe)
            $slugs = $this->guruWisdom->getAllIds($locale);

            if (empty($slugs)) {
                throw new \RuntimeException('Diagnose-Fehler: Es wurden keine .md Dateien für die Sprache ' . $locale . ' gefunden.');
            }

            // 2. Weisheiten parsen
            foreach ($slugs as $slug) {
                // Nur das Front Matter lesen (inkl. Vererbung aus der deutschen Datei), kein Markdown rendern
                $data = $this->guruWisdom->getFrontMatter($slug, $locale);

                $wisdoms[] = [
                    'slug'        => $slug,
                    'title'       => $data['title'] ?? $slug,
                    'subtitle'    => $data['subtitle'] ?? '',
                    'description' => $data['description'] ?? '',
                    'tags'        => $data['tags'] ?? [],
                    'categories'  => $data['categories'] ?? 'allgemein',
                    'date'        => $data['date'] ?? date('Y-m-d', (int)filemtime($this->guruWisdom->getFilePath($slug, $locale))),
                    'draft'       => (bool)($data['draft'] ?? false),
                ];
            }

            // 3. Chronologisch absteigend sortieren
            // Nach Zeitstempel statt per String-Vergleich, damit z. B. "2026-11-7" nicht vor "2026-11-10" landet
            usort($wisdoms, fn($a, $b) => (strtotime((string)$b['date']) ?: 0) <=> (strtotime((string)$a['date']) ?: 0));

            return $wisdoms;
        }, $ttl);

        // The cached index holds ALL wisdoms. The publication date is checked on every request
        // (cheap, no file access), so a wisdom appears exactly at its date, even if the index is cached.
        return self::applySchedule($wisdoms, new \DateTimeImmutable('now', new \DateTimeZone(self::TIMEZONE)), $draftsEnabled);
    }

    /**
     * Wisdoms with a date in the future are scheduled: they are hidden until that moment, everywhere
     * (overview, detail page, navigation, sitemap), because all of them read this list.
     * In dev (keepScheduled = true) they stay in the list, marked with 'scheduled' => true,
     * so they can be checked before they go live.
     *
     * The date is read as Berlin time: "2026-11-18" is visible from 18.11.2026 00:00,
     * "2026-11-18 08:00" from 08:00.
     *
     * @param array<int, array<string, mixed>> $wisdoms
     * @return array<int, array<string, mixed>>
     */
    public static function applySchedule(array $wisdoms, \DateTimeImmutable $now, bool $keepScheduled): array
    {
        $result = [];
        foreach ($wisdoms as $wisdom) {
            if (self::isScheduled($wisdom['date'] ?? null, $now)) {
                if (!$keepScheduled) {
                    continue;
                }
                $wisdom['scheduled'] = true;
            }
            $result[] = $wisdom;
        }

        return $result;
    }

    /**
     * Whether a date lies after $now. Empty or unreadable dates are never scheduled
     * (a typo must not make a wisdom vanish silently, that is covered by the front matter test).
     */
    public static function isScheduled(mixed $date, \DateTimeImmutable $now): bool
    {
        $value = trim((string)$date);
        if ($value === '') {
            return false;
        }

        try {
            $publishAt = new \DateTimeImmutable($value, $now->getTimezone());
        } catch (\Throwable) {
            return false;
        }

        return $publishAt > $now;
    }

    /**
     * Overview order: drafts first (they only exist in dev), then the published wisdoms.
     * The order inside both groups stays as it is (newest first). Pure function, no side effects.
     *
     * @param array<int, array<string, mixed>> $wisdoms
     * @return array<int, array<string, mixed>>
     */
    public static function draftsFirst(array $wisdoms): array
    {
        $drafts = [];
        $published = [];
        foreach ($wisdoms as $wisdom) {
            if (!empty($wisdom['draft'])) {
                $drafts[] = $wisdom;
            } else {
                $published[] = $wisdom;
            }
        }

        return array_merge($drafts, $published);
    }

    /**
     * Ermittelt die vorherige und nächste Weisheit für die Navigationspfeile.
     */
    public function getNeighbors(string $currentId, string $locale = 'de'): array
    {
        $wisdoms = $this->getSortedWisdoms($locale);
        $currentIndex = null;
        $totalCount = count($wisdoms);

        foreach ($wisdoms as $index => $wisdom) {
            if ($wisdom['slug'] === $currentId) {
                $currentIndex = $index;
                break;
            }
        }

        if ($currentIndex === null || $totalCount < 2) {
            return ['newer' => null, 'older' => null];
        }

        // Kreislauf-Logik (Karussell)
        $newerIndex = ($currentIndex === 0) ? ($totalCount - 1) : ($currentIndex - 1);
        $olderIndex = ($currentIndex === ($totalCount - 1)) ? 0 : ($currentIndex + 1);

        return [
            'newer' => $wisdoms[$newerIndex],
            'older' => $wisdoms[$olderIndex],
        ];
    }

    public function getLatestWisdom(string $locale = 'de'): ?array
    {
        $wisdoms = $this->getSortedWisdoms($locale);
        return !empty($wisdoms) ? $wisdoms[0] : null;
    }

    /**
     * Key of the cached index of a locale for a given content stamp.
     */
    private static function cacheKey(string $locale, string $stamp): string
    {
        return 'wisdom_sorted_index_' . $locale . '_' . $stamp;
    }

    /**
     * Entfernt den aktuellen Index (für eine bestimmte Sprache oder für alle).
     * Wegen des Content-Stamps im Key ist das nur noch nötig, um Platz zu sparen oder einen
     * Neuaufbau zu erzwingen; alte Einträge laufen von selbst ab.
     */
    public function clearCache(?string $locale = null): void
    {
        foreach ($locale !== null ? [$locale] : ['de', 'en'] as $lang) {
            $this->cache->remove(self::cacheKey($lang, $this->guruWisdom->getContentStamp($lang)));
        }
    }
}
