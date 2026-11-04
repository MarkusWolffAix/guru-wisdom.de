<?php

declare(strict_types=1);

namespace App\Service;

use Yiisoft\Cache\CacheInterface;
use Yiisoft\Aliases\Aliases;
use App\Service\GuruWisdomService;

class WisdomCacheService
{
    private string $wisdomPath;

    public function __construct(
        private CacheInterface $cache,
        private GuruWisdomService $guruWisdom,
        Aliases $aliases
    ) {
        $this->wisdomPath = $aliases->get('@public/wisdoms');
    }

    /**
     * Liefert alle sortierten Weisheiten für die gewünschte Sprache.
     * Nutzt für jede Sprache einen separaten Cache-Eintrag.
     */
    public function getSortedWisdoms(string $locale = 'de'): array
    {
        $cacheKey = 'wisdom_sorted_index_' . $locale;

        return $this->cache->getOrSet($cacheKey, function () use ($locale) {
            $wisdoms = [];

            // 1. Pfad bestimmen: bei 'en' nach /public/wisdoms/en/ suchen
            $baseDir = rtrim($this->wisdomPath, '/');
            $targetDir = ($locale === 'en') ? $baseDir . '/en' : $baseDir;

            // Fallback auf Standardpfad, falls /en/ nicht existiert oder leer ist
            if (!is_dir($targetDir) || empty(glob($targetDir . '/*.md'))) {
                $targetDir = $baseDir;
            }

            $searchPath = $targetDir . '/*.md';
            $files = glob($searchPath);

            if (empty($files)) {
                throw new \RuntimeException(
                    "Diagnose-Fehler: Ich suche im Pfad '" . $searchPath . "', finde aber keine .md Dateien."
                );
            }

            // 2. Weisheiten parsen
            foreach ($files as $file) {
                $slug = basename($file, '.md');
                // Sprache an GuruWisdomService übergeben
                $data = $this->guruWisdom->parseFile($slug, $locale);

                $wisdoms[] = [
                    'slug'        => $slug,
                    'title'       => $data['title'] ?? $slug,
                    'subtitle'    => $data['subtitle'] ?? '',
                    'description' => $data['description'] ?? '',
                    'tags'        => $data['tags'] ?? [],
                    'categories'  => $data['categories'] ?? 'allgemein',
                    'date'        => $data['date'] ?? date('Y-m-d', filemtime($file)),
                ];
            }

            // 3. Chronologisch absteigend sortieren
            usort($wisdoms, fn($a, $b) => strcmp((string)$b['date'], (string)$a['date']));

            return $wisdoms;
        }, 86400);
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
     * Leert den Cache (entweder für eine bestimmte Sprache oder für alle).
     */
    public function clearCache(?string $locale = null): void
    {
        if ($locale !== null) {
            $this->cache->remove('wisdom_sorted_index_' . $locale);
            return;
        }

        // Beide Caches und alten Legacy-Key bereinigen
        $this->cache->remove('wisdom_sorted_index_de');
        $this->cache->remove('wisdom_sorted_index_en');
        $this->cache->remove('wisdom_sorted_index');
    }
}
