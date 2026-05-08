<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

final class TranslateWisdomCommand extends Command
{
    protected static $defaultName = 'wisdom/translate-md';
    protected static $defaultDescription = 'Übersetzt Markdown-Dateien mit YAML-Frontmatter via DeepL';

    private string $deeplApiKey = 'DEIN_DEEPL_API_KEY_HIER'; 

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // Beispiel: Pfad zu deiner deutschen Datei und wo die englische hin soll
        $fileDe = __DIR__ . '/../../data/de/AbrahamLights.md';
        $fileEn = __DIR__ . '/../../data/en/AbrahamLights.md';

        if (!file_exists($fileDe)) {
            $io->error("Datei nicht gefunden: {$fileDe}");
            return Command::FAILURE;
        }

        $io->title('Starte Übersetzung der Markdown-Datei...');

        // 1. Datei einlesen und in Frontmatter (Metadaten) und Body (Text) aufteilen
        $content = file_get_contents($fileDe);
        
        // Nutzt einen Regulären Ausdruck, um den Block zwischen --- und --- zu finden
        if (!preg_match('/^\s*---\n(.*?)\n---\n(.*)/s', $content, $matches)) {
            $io->error("Ungültiges Dateiformat. YAML Frontmatter (---) fehlt.");
            return Command::FAILURE;
        }

        $yamlBlock = $matches[1];
        $markdownBody = $matches[2];

        // 2. YAML in ein PHP-Array umwandeln
        $metaData = Yaml::parse($yamlBlock);

        // 3. Metadaten übersetzen (nur die relevanten Felder!)
        $io->text('Übersetze Metadaten...');
        if (isset($metaData['title'])) {
            $metaData['title'] = $this->translateViaDeepL($metaData['title']);
        }
        if (isset($metaData['subtitle'])) {
            $metaData['subtitle'] = $this->translateViaDeepL($metaData['subtitle']);
        }
        if (isset($metaData['description'])) {
            $metaData['description'] = $this->translateViaDeepL($metaData['description']);
        }
        // Optional: Tags und Kategorien übersetzen, falls gewünscht.

        // 4. Den eigentlichen Markdown-Text übersetzen
        $io->text('Übersetze Haupttext...');
        $translatedBody = $this->translateViaDeepL($markdownBody);

        if (!$translatedBody) {
            $io->error('Fehler bei der Übersetzung des Haupttextes.');
            return Command::FAILURE;
        }

        // 5. Alles wieder zusammenbauen
        // Yaml::dump wandelt das PHP-Array wieder in einen YAML-String um
        $newYamlBlock = Yaml::dump($metaData);
        $newContent = "---\n" . $newYamlBlock . "---\n" . $translatedBody;

        // 6. Neue englische Datei speichern
        // Erstelle den Zielordner, falls er nicht existiert
        $dir = dirname($fileEn);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($fileEn, $newContent);

        $io->success("Datei erfolgreich übersetzt und gespeichert unter: {$fileEn}");

        return Command::SUCCESS;
    }

    private function translateViaDeepL(string $text): ?string
    {
        $url = 'https://api-free.deepl.com/v2/translate';
        
        $data = [
            'text' => [$text],
            'target_lang' => 'EN-US',
            'source_lang' => 'DE',
            // Wichtig für Markdown: DeepL soll die Formatierung (#, *, etc.) so gut es geht erhalten
            'preserve_formatting' => '1' 
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: DeepL-Auth-Key ' . $this->deeplApiKey,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return null;
        }

        $result = json_decode($response, true);
        return $result['translations'][0]['text'] ?? null;
    }
}