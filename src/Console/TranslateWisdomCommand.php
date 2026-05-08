<?php

declare(strict_types=1);

namespace App\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

final class TranslateWisdomCommand extends Command
{
    protected static $defaultName = 'wisdom/translate';
    protected static $defaultDescription = 'Übersetzt deutsche Markdown-Weisheiten ins Englische via Google Cloud';

    private string $sourceDir;
    private string $targetDir;

    public function __construct()
    {
        parent::__construct();
        // Pfade basierend auf deiner Struktur in public/wisdoms
        $this->sourceDir = dirname(__DIR__, 2) . '/public/wisdoms';
        $this->targetDir = $this->sourceDir . '/en';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // Key sicher aus der Umgebungsvariable laden
        $apiKey = $_ENV['GOOGLE_TRANSLATE_API_KEY'] ?? getenv('GOOGLE_TRANSLATE_API_KEY');

        if (empty($apiKey)) {
            $io->error('API-Key nicht gefunden! Stelle sicher, dass GOOGLE_TRANSLATE_API_KEY in der .env Datei steht.');
            return Command::FAILURE;
        }

        $files = glob($this->sourceDir . '/*.md');
        $io->title('Starte Übersetzung von ' . count($files) . ' Dateien');

        if (!is_dir($this->targetDir)) {
            mkdir($this->targetDir, 0775, true);
        }

        $client = new Client();
        $io->progressStart(count($files));

        foreach ($files as $file) {
            $filename = basename($file);
            $targetFile = $this->targetDir . '/' . $filename;

            // Überspringen, wenn Datei bereits existiert
            if (file_exists($targetFile)) {
                $io->progressAdvance();
                continue;
            }

            $content = file_get_contents($file);
            
            // 1. Zerlegen in Frontmatter und Body
            if (preg_match('/^---\n(.*?)\n---\n(.*)/s', $content, $matches)) {
                $yaml = Yaml::parse($matches[1]);
                $body = $matches[2];

                // 2. Metadaten übersetzen
                $yaml['title'] = $this->translate($client, $apiKey, $yaml['title'] ?? '');
                $yaml['subtitle'] = $this->translate($client, $apiKey, $yaml['subtitle'] ?? '');
                $yaml['description'] = $this->translate($client, $apiKey, $yaml['description'] ?? '');

                // 3. Body übersetzen
                $translatedBody = $this->translate($client, $apiKey, $body);

                // 4. Zusammenbauen und Speichern
                $newContent = "---\n" . Yaml::dump($yaml, 2, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK) . "---\n" . $translatedBody;
                file_put_contents($targetFile, $newContent);
            }

            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success('Übersetzung abgeschlossen. Die Dateien liegen in ' . $this->targetDir);

        return Command::SUCCESS;
    }

    private function translate(Client $client, string $key, string $text): string
    {
        if (empty(trim($text))) {
            return $text;
        }

        try {
            $response = $client->post('https://translation.googleapis.com/language/translate/v2', [
                'query' => ['key' => $key],
                'json' => [
                    'q' => $text,
                    'source' => 'de',
                    'target' => 'en',
                    'format' => 'text'
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $translated = $data['data']['translations'][0]['translatedText'] ?? $text;
            
            // Google escaped manchmal Zeichen, die wir im Markdown im Klartext wollen
            return htmlspecialchars_decode($translated, ENT_QUOTES);
            
        } catch (GuzzleException $e) {
            return "[Error: " . $e->getMessage() . "] " . $text;
        }
    }
}