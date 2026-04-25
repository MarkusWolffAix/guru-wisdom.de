<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class WisdomMarkdownTest extends TestCase
{
    public function testAllWisdomsHaveValidDate(): void
    {
        // 1. Pfad definieren (Passe diesen an deine Ordnerstruktur an)
        // __DIR__ ist das Verzeichnis, in dem dieser Test liegt.
        $path = __DIR__ . '/../../public/wisdoms/'; 
        $files = glob($path . '*.md');

        // 2. Sicherstellen, dass überhaupt Dateien gefunden wurden
        $this->assertNotEmpty($files, "Es wurden keine Markdown-Dateien im Ordner {$path} gefunden.");

        // 3. Jede Datei überprüfen
        foreach ($files as $file) {
            // Wir lesen wieder nur den Anfang der Datei (den YAML-Header)
            $content = file_get_contents($file, false, null, 0, 1024);
            $filename = basename($file);

            // Regulärer Ausdruck: Sucht nach "date: YYYY-MM-DD"
            // \d{4} = exakt 4 Ziffern, \d{2} = exakt 2 Ziffern
            $hasValidDatePattern = preg_match('/^date:\s*\d{4}-\d{2}-\d{2}\s*$/im', $content);

            // 4. Die eigentliche Test-Behauptung (Assertion)
            $this->assertTrue(
                (bool)$hasValidDatePattern,
                "❌ Fehler in Datei: {$filename}\nDas 'date'-Feld fehlt, ist falsch geschrieben oder hat nicht das Format 'YYYY-MM-DD'."
            );
            
            // Optional: Prüfen, ob das YAML-Frontmatter überhaupt korrekt geschlossen wird
            $hasFrontmatterEnding = strpos($content, '---') !== false;
            $this->assertTrue(
                $hasFrontmatterEnding,
                "❌ Fehler in Datei: {$filename}\nDas YAML-Frontmatter (---) ist fehlerhaft oder fehlt."
            );
        }
    }
}