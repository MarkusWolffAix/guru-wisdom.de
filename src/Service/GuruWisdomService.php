<?php

declare(strict_types=1);

namespace App\Service;

use Yiisoft\Aliases\Aliases;
use cebe\markdown\GithubMarkdown;

/**
 * BaseGuruWisdom
 *
 * Provides core functionalities to parse and render wisdom markdown files.
 *
 * @author Markus Wolff <markus.wolff@guru-wisdom.com>
 * @since 2026-02-13
 */
class GuruWisdomService
{
    /**
     * @var Aliases Dependency for resolving Yii path aliases.
     */
    protected Aliases $aliases;

    /**
     * Initializes the class and injects required Yii3 dependencies.
     *
     * @param Aliases $aliases Component to resolve directory aliases.
     */
    public function __construct(
        Aliases $aliases
    ) {
        $this->aliases = $aliases;
    }

    /**
     * Resolves the absolute file path for a given wisdom ID with language fallback.
     * Checks /public/wisdoms/en/{id}.md first for English, then falls back to /public/wisdoms/{id}.md.
     *
     * @param string $id     The wisdom ID.
     * @param string $locale The language code ('de' or 'en').
     * @return string The absolute path to the resolved markdown file.
     */
    public function getFilePath(string $id, string $locale = 'de'): string
    {
        if ($locale === 'en') {
            $enPath = $this->aliases->get('@public/wisdoms/en/' . $id . '.md');
            if (file_exists($enPath)) {
                return $enPath;
            }
        }

        return $this->aliases->get('@public/wisdoms/' . $id . '.md');
    }

    /**
     * Sanitizes the requested ID and ensures the corresponding file exists.
     *
     * @param string|mixed $id     The requested wisdom ID.
     * @param string       $locale The language code ('de' or 'en').
     * @return string The sanitized and valid file ID.
     */
    public function sanitizeId($id, string $locale = 'de'): string
    {
        $filePath = $this->getFilePath((string)$id, $locale);

        if (!file_exists($filePath)) {
            $basePath = ($locale === 'en' && is_dir($this->aliases->get('@public/wisdoms/en/')))
                ? $this->aliases->get('@public/wisdoms/en/')
                : $this->aliases->get('@public/wisdoms/');

            $files = glob($basePath . '*.md');
            if (!empty($files)) {
                $filePath = $files[array_rand($files)];
            }
        }

        return basename($filePath, '.md');
    }

    /**
     * Generates the HTML string for the wisdom's image.
     * Uses /images/en/ for English requests, relying on NGINX for fallback.
     *
     * @param string|mixed $id     The unique identifier of the wisdom.
     * @param string       $locale The language code ('de' or 'en').
     * @return string The HTML `<picture>` element.
     */
    public function getImageHtml($id, string $locale = 'de'): string
    {
        $subDir = ($locale === 'en') ? 'en/' : '';
        $url = "https://media.guru-wisdom.de/images/" . $subDir;

        return '
        <picture>
            <source srcset="' . $url . $id . '.webp" type="image/webp">
            <img src="' . $url . $id . '.jpg" alt="Image of Wisdom ' . htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8') . '" class="img-fluid" fetchpriority="high">
        </picture>
        ';
    }

    /**
     * Generates the HTML string for the wisdom's audio player.
     * Uses /audio/en/ for English requests, relying on NGINX for fallback.
     *
     * @param string|mixed $id     The unique identifier of the wisdom.
     * @param string       $locale The language code ('de' or 'en').
     * @return string The HTML `<audio>` element.
     */
    public function getAudioHtml($id, string $locale = 'de'): string
    {
        $subDir = ($locale === 'en') ? 'en/' : '';
        $url = "https://media.guru-wisdom.de/audio/" . $subDir . $id . ".mp3";

        return '<audio controls preload="metadata"> <source src="' . $url . '" type="audio/mpeg"> </audio>';
    }

    /**
     * Retrieves the previous, current, and next IDs for navigation.
     *
     * @param string|null $id     The current wisdom ID.
     * @param string      $locale The language code ('de' or 'en').
     * @return array{prev: string|null, current: string|null, next: string|null}
     */
    public function getNavigationIds(?string $id = null, string $locale = 'de'): array
    {
        $sortedId = $this->getSortedWisdomIds(null, $locale);
        $count = count($sortedId);
        $currentIndex = array_search($id, $sortedId, true);

        $prevId = ($currentIndex !== false && $currentIndex > 0) ? $sortedId[$currentIndex - 1] : null;
        $nextId = ($currentIndex !== false && $currentIndex < $count - 1) ? $sortedId[$currentIndex + 1] : null;

        return ["prev" => $prevId, "current" => $id, "next" => $nextId];
    }

    /**
     * Processes custom placeholders in a string and converts them into HTML components.
     *
     * @param string $text   The raw text containing placeholders.
     * @param string $locale The language code ('de' or 'en').
     * @return string The processed text with valid HTML tags.
     */
    public function processPlaceholders(string $text, string $locale = 'de'): string
    {
        $mediaSubDir = ($locale === 'en') ? 'en/' : '';

        // 1. YouTube Placeholders (Zwei-Klick-Lösung)
        $text = preg_replace_callback('/\[youtube:([a-zA-Z0-9_-]+)\]/', function (array $matches) {
            $videoId = $matches[1];
            $safeUrl = 'https://www.youtube-nocookie.com/embed/' . htmlspecialchars($videoId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            return '<div class="two-click-container my-4" data-type="youtube" data-src="' . $safeUrl . '" style="max-width: 640px;">
                <div class="two-click-placeholder p-4 text-center" style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px;">
                    <p class="mb-3"><strong>Externes YouTube-Video</strong></p>
                    <p class="mb-3 small">Mit dem Klick auf "Video laden" stimmst du zu, dass deine IP-Adresse an Server von Google übermittelt wird.
                    <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Mehr Informationen</a>, siehe auch
                    <a href="./privacy#externe-medien"> Datenschutz. </a>
                    </p>
                    <button class="btn btn-primary mt-1 load-media-btn">Video laden</button>
                </div>
            </div>';
        }, $text);

        // 2. Spotify Placeholders
        $text = preg_replace_callback('/\[spotify:(track|album|playlist|artist|episode|show):([a-zA-Z0-9]+)\]/', function (array $matches) {
            $type = $matches[1];
            $spotifyId = $matches[2];

            $safeType = htmlspecialchars($type, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeId = htmlspecialchars($spotifyId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeUrl = 'https://open.spotify.com/embed/' . $safeType . '/' . $safeId . '?utm_source=generator';

            return '<div class="two-click-container my-4" data-type="spotify" data-src="' . $safeUrl . '" style="max-width: 640px;">
                <div class="two-click-placeholder p-4 text-center" style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 12px;">
                    <p class="mb-3"><strong>Externer Spotify-Player</strong></p>
                    <p class="mb-3 small">Mit dem Klick auf "Inhalt laden" stimmst du zu, dass deine IP-Adresse an Server von Spotify übermittelt wird
                    siehe <a href="./privacy#externe-medien"> Datenschutz </a>.</p>
                    <button class="btn btn-success mt-1 load-media-btn">Inhalt laden</button>
                </div>
            </div>';
        }, $text);

        // 3. Image Placeholders
        $text = preg_replace_callback('/\[image:([^\|\]]+)(?:\|([^\]]+))?\]/', function (array $matches) use ($mediaSubDir) {
            $rawName = trim($matches[1]);
            $filename = basename($rawName);
            $baseName = pathinfo($filename, PATHINFO_FILENAME);

            $baseUrl = "https://media.guru-wisdom.de/images/" . $mediaSubDir;
            $webpUrl = $baseUrl . $baseName . ".webp";
            $fallbackUrl = $baseUrl . $baseName . ".jpg";
            $altText = isset($matches[2]) ? trim($matches[2]) : '';

            $safeWebpUrl = htmlspecialchars($webpUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeFallbackUrl = htmlspecialchars($fallbackUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeAlt = htmlspecialchars($altText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            return '<picture>
                <source srcset="' . $safeWebpUrl . '" type="image/webp">
                <img src="' . $safeFallbackUrl . '" alt="' . $safeAlt . '" class="img-fluid my-4" style="max-width: 100%; height: auto; border-radius: 8px;">
            </picture>';
        }, $text);

        // 4. Video Placeholders
        $text = preg_replace_callback('/\[video:([^\]]+)\]/', function (array $matches) use ($mediaSubDir) {
            $rawId = trim($matches[1]);
            $filename = basename($rawId);
            $videoUrl = "https://media.guru-wisdom.de/video/" . $mediaSubDir . $filename . ".mp4";
            $safeVideoUrl = htmlspecialchars($videoUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            return '<div class="my-4 text-center">
                <video controls preload="metadata" style="max-width: 100%; width: 640px; border-radius: 8px; background-color: #000;">
                    <source src="' . $safeVideoUrl . '" type="video/mp4">
                    Dein Browser unterstützt das Video-Tag nicht. Bitte aktualisiere deinen Browser.
                </video>
            </div>';
        }, $text);

        return $text;
    }

    /**
     * Parses the markdown file, handles fallbacks, and renders the HTML.
     *
     * @param string $id       The unique identifier of the wisdom.
     * @param string $locale   The language code ('de' or 'en').
     * @param bool   $autoSave Whether to auto-save structural changes back to the file.
     * @return array
     */
    public function parseFile(string $id, string $locale = 'de', bool $autoSave = false): array
    {
        $filePath = $this->getFilePath($id, $locale);
        $content = file_get_contents($filePath);

        if ($content === false) {
            return [];
        }

        $data = $this->extractFrontMatter($content);
        
        // Step 2: Apply fallbacks and split colon-headings
        $needsUpdate = $this->applyFallbacks($data);

        // Step 3: Auto-save the raw markdown if needed
        // $isDevMode = false; // Adjust this according to your Yii3 environment logic
        // if ($autoSave && $needsUpdate && $isDevMode) {
        //     $this->updateFile($filePath, $data);
        // }

        // Step 4: Convert raw markdown to HTML, THEN process placeholders
        $parser = new GithubMarkdown();
        $parsedHtml = $parser->parse($data['raw_markdown']);

        // Replace placeholders in the parsed HTML
        $data['htmloutput'] = $this->processPlaceholders($parsedHtml);

        // Remove the raw markdown from the output array as the view only needs HTML
        unset($data['raw_markdown']);

        return $data;
    }
    /**
     * Extracts YAML Front Matter and separates the raw text.
     *
     * @param string $content The raw content of the markdown file.
     * @return array Parsed front matter properties along with the 'raw_markdown' string.
     */
    private function extractFrontMatter(string $content): array
    {
        $data = ['raw_markdown' => $content];

        if (preg_match('/^---\s*[\r\n]+(.*?)[\r\n]+---\s*[\r\n]+(.*)$/s', $content, $matches)) {
            $data['raw_markdown'] = trim($matches[2]);
            $lines = preg_split('/[\r\n]+/', trim($matches[1]));

            foreach ($lines as $line) {
                if (preg_match('/^([a-zA-Z0-9_-]+)\s*:\s*(.*)$/', trim($line), $lineMatches)) {
                    $key = strtolower(trim($lineMatches[1]));
                    $value = trim($lineMatches[2], " \t\n\r\0\x0B\"'");

                    if (preg_match('/^\[(.*)\]$/', $value, $arrayMatches)) {
                        $value = array_map('trim', explode(',', $arrayMatches[1]));
                    }
                    $data[$key] = $value;
                }
            }
        }

        return $data;
    }

    /**
     * Handles missing titles and splits H1 headings containing a colon.
     *
     * @param array &$data A reference to the parsed data array.
     * @return bool True if any modifications were made that would require a file update.
     */
    private function applyFallbacks(array &$data): bool
    {
        $updated = false;

        // Detect H1 with a colon and rewrite the raw markdown structure
        $data['raw_markdown'] = preg_replace_callback('/^#\s+([^:\n]+):\s*(.+)$/m', function(array $matches) use (&$data, &$updated) {
            $mainTitle = trim($matches[1]);
            $subTitle = trim($matches[2]);

            if (empty($data['title'])) {
                $data['title'] = $mainTitle;
            }
            if (empty($data['subtitle'])) {
                $data['subtitle'] = $subTitle;
            }

            $updated = true;
            return '# ' . $mainTitle . "\n## " . $subTitle;
        }, $data['raw_markdown'], -1, $count);

        if ($count > 0) {
            $updated = true;
        }

        // Standard fallback for Title
        if (empty($data['title'])) {
            if (preg_match('/^#\s+(.+)$/m', $data['raw_markdown'], $titleMatches)) {
                $data['title'] = trim($titleMatches[1]);
                $updated = true;
            } else {
                $data['title'] = 'Unknown Wisdom';
            }
        }

        // Standard fallback for Subtitle
        if (empty($data['subtitle']) && preg_match('/^##\s+(.+)$/m', $data['raw_markdown'], $subMatches)) {
            $data['subtitle'] = trim($subMatches[1]);
            $updated = true;
        }

        return $updated;
    }
    /**
     * Writes the updated data back into the physical markdown file.
     *
     * @param string $filePath The absolute path to the file.
     * @param array  $data     The parsed data including the raw markdown to write.
     * @return void
     */
    public function getSortedWisdomIds(?array $ids = null, string $locale = 'de'): array
    {
        $path = ($locale === 'en' && is_dir($this->aliases->get('@public/wisdoms/en/')))
            ? $this->aliases->get('@public/wisdoms/en/')
            : $this->aliases->get('@public/wisdoms/');

        $wisdomsToSort = [];

        if (!empty($ids)) {
            foreach ($ids as $id) {
                $file = $path . $id . '.md';
                if (!file_exists($file)) {
                    $file = $this->aliases->get('@public/wisdoms/' . $id . '.md');
                }
                if (file_exists($file)) {
                    $wisdomsToSort[$id] = $file;
                }
            }
        } else {
            $files = glob($path . '*.md');
            if ($files !== false) {
                foreach ($files as $file) {
                    $id = basename($file, '.md');
                    $wisdomsToSort[$id] = $file;
                }
            }
        }

        // 2. Read the date and prepare the array
        $filesWithDate = [];
        foreach ($wisdomsToSort as $id => $file) {
            // Read only the first 1024 bytes for performance
            $content = file_get_contents($file, false, null, 0, 1024);
            $timestamp = 0; // Fallback

            if ($content !== false && preg_match('/^date:\s*(.+)$/im', $content, $matches)) {
                $timestamp = strtotime(trim($matches[1])) ?: 0;
            }

            $filesWithDate[] = [
                'id' => $id,
                'timestamp' => $timestamp
            ];
        }

        // 3. Sort descending by timestamp (Newest first)
        usort($filesWithDate, function($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        // 4. Return only the array of IDs (e.g., ['GoldenThread', 'SilverLining', ...])
        return array_column($filesWithDate, 'id');
    }
}
