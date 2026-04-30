<?php

declare(strict_types=1);

namespace App\Service;

use Yiisoft\Cache\CacheInterface;
use Yiisoft\Aliases\Aliases; // <-- WICHTIG: Das Alias-Paket importieren
use App\Service\GuruWisdomService;

class WisdomCacheService
{
    private string $wisdomPath;

    public function __construct(
        private CacheInterface $cache,
        private GuruWisdomService $guruWisdom,
        Aliases $aliases // <-- Yii3 reicht uns die Aliase automatisch herein
    ) {
        // Wir nutzen dein exaktes Alias, um den absolut korrekten Pfad zu holen!
        $this->wisdomPath = $aliases->get('@public/wisdoms');
    }

    public function getSortedWisdoms(): array
    {
        return $this->cache->getOrSet('wisdom_sorted_index', function () {
            $wisdoms = [];
            
            // rtrim sichert uns ab, falls am Ende des Alias ein '/' steht oder nicht
            $searchPath = rtrim($this->wisdomPath, '/') . '/*.md';
            $files = glob($searchPath);

            // Unser kleiner Diagnose-Wächter (kann später auch entfernt werden)
            if (empty($files)) {
                throw new \RuntimeException(
                    "Diagnose-Fehler: Ich suche im Pfad '" . $searchPath . "', finde aber keine .md Dateien."
                );
            }

            foreach ($files as $file) {
                $slug = basename($file, '.md');
                $data = $this->guruWisdom->parseFile($slug);
                
                $wisdoms[] = [
                    'slug'  => $slug,
                    'title' => $data['title'] ?? $slug,
                    'date'  => $data['date'] ?? date('Y-m-d', filemtime($file))
                ];
            }

            usort($wisdoms, fn($a, $b) => strcmp((string)$b['date'], (string)$a['date']));

            return $wisdoms;
        }, 86400); 
    }

public function getNeighbors(string $currentId): array
    {
        $wisdoms = $this->getSortedWisdoms();
        $currentIndex = null;
        $totalCount = count($wisdoms);

        // Position des aktuellen Eintrags finden
        foreach ($wisdoms as $index => $wisdom) {
            if ($wisdom['slug'] === $currentId) {
                $currentIndex = $index;
                break;
            }
        }

        // Falls der Beitrag nicht existiert oder es weniger als 2 Beiträge gibt
        if ($currentIndex === null || $totalCount < 2) {
            return ['newer' => null, 'older' => null];
        }

        // --- Die Kreislauf-Logik ---
        
        // Neuer (Links): Wenn wir am Anfang sind (0), springen wir zum letzten Element (totalCount - 1)
        $newerIndex = ($currentIndex === 0) ? ($totalCount - 1) : ($currentIndex - 1);
        
        // Älter (Rechts): Wenn wir am Ende sind (totalCount - 1), springen wir zum ersten Element (0)
        $olderIndex = ($currentIndex === ($totalCount - 1)) ? 0 : ($currentIndex + 1);

        return [
            'newer' => $wisdoms[$newerIndex],
            'older' => $wisdoms[$olderIndex],
        ];
    }

    public function getLatestWisdom(): ?array
    {
        $wisdoms = $this->getSortedWisdoms();
        return !empty($wisdoms) ? $wisdoms[0] : null;
    }
}