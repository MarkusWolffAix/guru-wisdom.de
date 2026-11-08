<?php

declare(strict_types=1);

namespace App\Service;

use cebe\markdown\GithubMarkdown;
use InvalidArgumentException;
use RuntimeException;
use Yiisoft\Aliases\Aliases;

/**
 * GuruWisdomService
 *
 * Provides core functionalities to parse and render wisdom markdown files.
 *
 * @author Markus Wolff <markus.wolff@guru-wisdom.com>
 * @since 2026-02-13
 */
class GuruWisdomService
{
    private const MEDIA_BASE_URL = 'https://media.guru-wisdom.de';

    /**
     * Front matter keys that are never inherited from the default-language file, because their
     * value is language specific. A locale file that lacks them falls back to its own H1 instead.
     */
    private const NON_INHERITED_KEYS = ['title', 'subtitle', 'description'];

    /**
     * Texts for the two-click placeholders, per locale.
     */
    private const TEXTS = [
        'de' => [
            'youtube_title' => 'Externes YouTube-Video',
            'youtube_text' => 'Mit dem Klick auf "Video laden" stimmst du zu, dass deine IP-Adresse an Server von Google übermittelt wird.',
            'youtube_more' => 'Mehr Informationen',
            'see_also' => 'siehe auch',
            'privacy' => 'Datenschutz',
            'youtube_button' => 'Video laden',
            'spotify_title' => 'Externer Spotify-Player',
            'spotify_text' => 'Mit dem Klick auf "Inhalt laden" stimmst du zu, dass deine IP-Adresse an Server von Spotify übermittelt wird, siehe',
            'content_button' => 'Inhalt laden',
            'video_unsupported' => 'Dein Browser unterstützt das Video-Tag nicht. Bitte aktualisiere deinen Browser.',
            'image_alt' => 'Bild zur Weisheit',
        ],
        'en' => [
            'youtube_title' => 'External YouTube video',
            'youtube_text' => 'By clicking "Load video" you agree that your IP address will be transmitted to Google servers.',
            'youtube_more' => 'More information',
            'see_also' => 'see also',
            'privacy' => 'Privacy policy',
            'youtube_button' => 'Load video',
            'spotify_title' => 'External Spotify player',
            'spotify_text' => 'By clicking "Load content" you agree that your IP address will be transmitted to Spotify servers, see',
            'content_button' => 'Load content',
            'video_unsupported' => 'Your browser does not support the video tag. Please update your browser.',
            'image_alt' => 'Image of wisdom',
        ],
    ];

    /**
     * @var Aliases Dependency for resolving Yii path aliases.
     */
    protected Aliases $aliases;

    /**
     * In-request cache of the sorted ID list per locale.
     *
     * @var array<string, list<string>>
     */
    private array $sortedIdsCache = [];

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
     * Checks whether an ID only contains safe characters (no path separators, no dots).
     *
     * @param string $id The wisdom ID.
     * @return bool True if the ID is valid.
     */
    public function isValidId(string $id): bool
    {
        return preg_match('/^[A-Za-z0-9_-]+$/', $id) === 1;
    }

    /**
     * Resolves the absolute file path for a given wisdom ID with language fallback.
     * Checks /public/wisdoms/en/{id}.md first for English, then falls back to /public/wisdoms/{id}.md.
     *
     * @param string $id     The wisdom ID.
     * @param string $locale The language code ('de' or 'en').
     * @return string The absolute path to the resolved markdown file.
     * @throws InvalidArgumentException If the ID contains invalid characters.
     */
    public function getFilePath(string $id, string $locale = 'de'): string
    {
        if (!$this->isValidId($id)) {
            throw new InvalidArgumentException('Invalid wisdom ID.');
        }

        if ($locale === 'en') {
            $enPath = $this->aliases->get('@public/wisdoms/en/' . $id . '.md');
            if (is_file($enPath)) {
                return $enPath;
            }
        }

        return $this->aliases->get('@public/wisdoms/' . $id . '.md');
    }

    /**
     * Sanitizes the requested ID and ensures the corresponding file exists.
     * Invalid or unknown IDs fall back to a random existing wisdom.
     *
     * @param string|mixed $id     The requested wisdom ID.
     * @param string       $locale The language code ('de' or 'en').
     * @return string The sanitized and valid file ID.
     * @throws RuntimeException If no wisdom files exist at all.
     */
    public function sanitizeId($id, string $locale = 'de'): string
    {
        $id = is_scalar($id) ? (string)$id : '';

        if ($this->isValidId($id) && is_file($this->getFilePath($id, $locale))) {
            return $id;
        }

        $files = glob($this->getWisdomsDirectory($locale) . '*.md');
        if ($files === false || $files === []) {
            throw new RuntimeException('No wisdom files found.');
        }

        return basename($files[array_rand($files)], '.md');
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
        $url = self::MEDIA_BASE_URL . '/images/' . $subDir . rawurlencode((string)$id);

        $safeWebp = htmlspecialchars($url . '.webp', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeJpg = htmlspecialchars($url . '.jpg', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeAlt = htmlspecialchars(
            $this->t('image_alt', $locale) . ' ' . (string)$id,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );

        return '
        <picture>
            <source srcset="' . $safeWebp . '" type="image/webp">
            <img src="' . $safeJpg . '" alt="' . $safeAlt . '" class="img-fluid" fetchpriority="high">
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
        $url = self::MEDIA_BASE_URL . '/audio/' . $subDir . rawurlencode((string)$id) . '.mp3';
        $safeUrl = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<audio controls preload="metadata"> <source src="' . $safeUrl . '" type="audio/mpeg"> </audio>';
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
        $sortedIds = $this->getSortedWisdomIds(null, $locale);
        $count = count($sortedIds);
        $currentIndex = $id === null ? false : array_search($id, $sortedIds, true);

        $prevId = ($currentIndex !== false && $currentIndex > 0) ? $sortedIds[$currentIndex - 1] : null;
        $nextId = ($currentIndex !== false && $currentIndex < $count - 1) ? $sortedIds[$currentIndex + 1] : null;

        return ['prev' => $prevId, 'current' => $id, 'next' => $nextId];
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
        $t = fn(string $key): string => $this->t($key, $locale);

        // 1. YouTube placeholders (two-click solution)
        $text = preg_replace_callback('/\[youtube:([a-zA-Z0-9_-]+)\]/', function (array $matches) use ($t) {
            $safeUrl = 'https://www.youtube-nocookie.com/embed/'
                . htmlspecialchars($matches[1], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            return '<div class="two-click-container my-4" data-type="youtube" data-src="' . $safeUrl . '" style="max-width: 640px;">
                <div class="two-click-placeholder p-4 text-center" style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px;">
                    <p class="mb-3"><strong>' . $t('youtube_title') . '</strong></p>
                    <p class="mb-3 small">' . $t('youtube_text') . '
                    <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">' . $t('youtube_more') . '</a>, ' . $t('see_also') . '
                    <a href="./privacy#externe-medien">' . $t('privacy') . '</a>.
                    </p>
                    <button class="btn btn-primary mt-1 load-media-btn">' . $t('youtube_button') . '</button>
                </div>
            </div>';
        }, $text);

        // 2. Spotify placeholders
        $text = preg_replace_callback(
            '/\[spotify:(track|album|playlist|artist|episode|show):([a-zA-Z0-9]+)\]/',
            function (array $matches) use ($t) {
                $safeType = htmlspecialchars($matches[1], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $safeId = htmlspecialchars($matches[2], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $safeUrl = 'https://open.spotify.com/embed/' . $safeType . '/' . $safeId . '?utm_source=generator';

                return '<div class="two-click-container my-4" data-type="spotify" data-src="' . $safeUrl . '" style="max-width: 640px;">
                <div class="two-click-placeholder p-4 text-center" style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 12px;">
                    <p class="mb-3"><strong>' . $t('spotify_title') . '</strong></p>
                    <p class="mb-3 small">' . $t('spotify_text') . ' <a href="./privacy#externe-medien">' . $t('privacy') . '</a>.</p>
                    <button class="btn btn-success mt-1 load-media-btn">' . $t('content_button') . '</button>
                </div>
            </div>';
            },
            $text
        );

        // 3. Image placeholders
        $text = preg_replace_callback(
            '/\[image:([^\|\]]+)(?:\|([^\]]+))?\]/',
            function (array $matches) use ($mediaSubDir) {
                $filename = basename(trim($matches[1]));
                $baseName = pathinfo($filename, PATHINFO_FILENAME);

                $baseUrl = self::MEDIA_BASE_URL . '/images/' . $mediaSubDir . rawurlencode($baseName);
                $altText = isset($matches[2]) ? trim($matches[2]) : '';

                $safeWebpUrl = htmlspecialchars($baseUrl . '.webp', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $safeFallbackUrl = htmlspecialchars($baseUrl . '.jpg', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $safeAlt = htmlspecialchars($altText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                return '<picture>
                <source srcset="' . $safeWebpUrl . '" type="image/webp">
                <img src="' . $safeFallbackUrl . '" alt="' . $safeAlt . '" class="img-fluid my-4" style="max-width: 100%; height: auto; border-radius: 8px;">
            </picture>';
            },
            $text
        );

        // 4. Video placeholders
        $text = preg_replace_callback(
            '/\[video:([^\]]+)\]/',
            function (array $matches) use ($mediaSubDir, $t) {
                $filename = basename(trim($matches[1]));
                $videoUrl = self::MEDIA_BASE_URL . '/video/' . $mediaSubDir . rawurlencode($filename) . '.mp4';
                $safeVideoUrl = htmlspecialchars($videoUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                return '<div class="my-4 text-center">
                <video controls preload="metadata" style="max-width: 100%; width: 640px; border-radius: 8px; background-color: #000;">
                    <source src="' . $safeVideoUrl . '" type="video/mp4">
                    ' . $t('video_unsupported') . '
                </video>
            </div>';
            },
            $text
        );

        return $text;
    }

    /**
     * Parses the markdown file, handles fallbacks, and renders the HTML.
     *
     * @param string $id       The unique identifier of the wisdom.
     * @param string $locale   The language code ('de' or 'en').
     * @param bool   $autoSave Deprecated and ignored. Kept for backwards compatibility.
     * @return array Front matter data plus 'htmloutput'; empty array if the file cannot be read.
     */
    public function parseFile(string $id, string $locale = 'de', bool $autoSave = false): array
    {
        $data = $this->loadMergedData($id, $locale);
        if ($data === null) {
            return [];
        }

        $this->applyFallbacks($data);

        // Replace placeholders by neutral tokens BEFORE markdown parsing, so that characters like
        // "_" or "*" in YouTube IDs, file names or alt texts cannot be turned into <em>/<strong>.
        $stash = [];
        $markdown = $this->protectPlaceholders($data['raw_markdown'], $locale, $stash);

        $parser = new GithubMarkdown();
        $html = $parser->parse($markdown);

        // Restore the real HTML. Unwrap block elements from the <p> the parser put around the token.
        $replacements = [];
        foreach ($stash as $token => $placeholderHtml) {
            $replacements['<p>' . $token . '</p>'] = $placeholderHtml;
            $replacements[$token] = $placeholderHtml;
        }
        $data['htmloutput'] = strtr($html, $replacements);

        unset($data['raw_markdown']);

        return $data;
    }

    /**
     * Returns only the front matter of a wisdom, without rendering the markdown.
     *
     * For a non-default locale the front matter of the default (German) file is the base, and the
     * keys of the locale file override it. So a translation only needs to contain what differs.
     * 'title', 'subtitle' and 'description' are never inherited. 'id' is always the file name.
     * Title and subtitle fall back to the headings of the markdown, as in parseFile().
     *
     * @param string $id     The wisdom ID.
     * @param string $locale The language code ('de' or 'en').
     * @return array Front matter data; empty array if the file cannot be read.
     */
    public function getFrontMatter(string $id, string $locale = 'de'): array
    {
        $data = $this->loadMergedData($id, $locale);
        if ($data === null) {
            return [];
        }

        // Same title/subtitle fallbacks as parseFile(), so both methods return the same front matter.
        $this->applyFallbacks($data);
        unset($data['raw_markdown']);

        return $data;
    }

    /**
     * Reads a wisdom file, merges the default-language front matter below it and sets the ID.
     *
     * @param string $id     The wisdom ID.
     * @param string $locale The language code ('de' or 'en').
     * @return array|null Front matter plus 'raw_markdown', or null if the file cannot be read.
     */
    private function loadMergedData(string $id, string $locale): ?array
    {
        if (!$this->isValidId($id)) {
            return null;
        }

        $filePath = $this->getFilePath($id, $locale);
        if (!is_file($filePath)) {
            return null;
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            return null;
        }

        $data = $this->extractFrontMatter($content);

        $basePath = $this->aliases->get('@public/wisdoms/' . $id . '.md');
        if ($basePath !== $filePath && is_file($basePath)) {
            $baseContent = file_get_contents($basePath);
            if ($baseContent !== false) {
                $base = $this->extractFrontMatter($baseContent);
                unset($base['raw_markdown']);

                foreach ($base as $key => $value) {
                    if (in_array($key, self::NON_INHERITED_KEYS, true)) {
                        continue;
                    }
                    if (!array_key_exists($key, $data) || $data[$key] === '' || $data[$key] === []) {
                        $data[$key] = $value;
                    }
                }
            }
        }

        // The file name is the ID. A front matter 'id' is ignored.
        $data['id'] = $id;

        return $data;
    }

    /**
     * Returns wisdom IDs sorted by their front matter date, newest first.
     *
     * @param array|null $ids    Optional subset of IDs to sort. If empty, all wisdoms are used.
     * @param string     $locale The language code ('de' or 'en').
     * @return list<string> Sorted IDs.
     */
    public function getSortedWisdomIds(?array $ids = null, string $locale = 'de'): array
    {
        $useCache = empty($ids);
        if ($useCache && isset($this->sortedIdsCache[$locale])) {
            return $this->sortedIdsCache[$locale];
        }

        $path = $this->getWisdomsDirectory($locale);
        $wisdomsToSort = [];

        if (!empty($ids)) {
            foreach ($ids as $id) {
                $id = (string)$id;
                if (!$this->isValidId($id)) {
                    continue;
                }

                $file = $path . $id . '.md';
                if (!is_file($file)) {
                    $file = $this->aliases->get('@public/wisdoms/' . $id . '.md');
                }
                if (is_file($file)) {
                    $wisdomsToSort[$id] = $file;
                }
            }
        } else {
            $files = glob($path . '*.md');
            if ($files !== false) {
                foreach ($files as $file) {
                    $wisdomsToSort[basename($file, '.md')] = $file;
                }
            }
        }

        // Read only the first 1024 bytes of each file for performance
        $filesWithDate = [];
        foreach ($wisdomsToSort as $id => $file) {
            $timestamp = $this->readDateTimestamp($file);

            // A translation without its own date uses the date of the default-language file.
            if ($timestamp === 0) {
                $basePath = $this->aliases->get('@public/wisdoms/' . $id . '.md');
                if ($basePath !== $file && is_file($basePath)) {
                    $timestamp = $this->readDateTimestamp($basePath);
                }
            }

            $filesWithDate[] = [
                // PHP converts numeric string array keys to int, so cast back to string.
                'id' => (string)$id,
                'timestamp' => $timestamp,
            ];
        }

        // Newest first; ties are sorted by ID so the order stays stable
        usort($filesWithDate, static function (array $a, array $b): int {
            return [$b['timestamp'], $a['id']] <=> [$a['timestamp'], $b['id']];
        });

        $sortedIds = array_column($filesWithDate, 'id');

        if ($useCache) {
            $this->sortedIdsCache[$locale] = $sortedIds;
        }

        return $sortedIds;
    }

    /**
     * Reads the front matter date of a file (first 1024 bytes only) as a Unix timestamp.
     *
     * @param string $file Absolute file path.
     * @return int The timestamp, or 0 if there is no valid date.
     */
    private function readDateTimestamp(string $file): int
    {
        $content = file_get_contents($file, false, null, 0, 1024);
        if ($content !== false && preg_match('/^date:[ \t]*(.+)$/im', $content, $matches)) {
            return strtotime(trim($matches[1], " \t\n\r\0\x0B\"'")) ?: 0;
        }

        return 0;
    }

    /**
     * Returns the wisdom directory for a locale (with trailing slash), falling back to the default one.
     *
     * @param string $locale The language code ('de' or 'en').
     * @return string Absolute directory path.
     */
    private function getWisdomsDirectory(string $locale): string
    {
        if ($locale === 'en') {
            $enDir = $this->aliases->get('@public/wisdoms/en/');
            if (is_dir($enDir)) {
                return $enDir;
            }
        }

        return $this->aliases->get('@public/wisdoms/');
    }

    /**
     * Returns a translated text for the given key, falling back to German.
     *
     * @param string $key    The text key.
     * @param string $locale The language code ('de' or 'en').
     * @return string The text.
     */
    private function t(string $key, string $locale): string
    {
        return self::TEXTS[$locale][$key] ?? self::TEXTS['de'][$key] ?? $key;
    }

    /**
     * Replaces all placeholders with unique tokens and stores the rendered HTML in $stash.
     *
     * @param string               $markdown The raw markdown.
     * @param string               $locale   The language code ('de' or 'en').
     * @param array<string,string> $stash    Receives token => HTML.
     * @return string Markdown with tokens instead of placeholders.
     */
    private function protectPlaceholders(string $markdown, string $locale, array &$stash): string
    {
        $pattern = '/\[(?:youtube:[a-zA-Z0-9_-]+'
            . '|spotify:(?:track|album|playlist|artist|episode|show):[a-zA-Z0-9]+'
            . '|image:[^\|\]]+(?:\|[^\]]+)?'
            . '|video:[^\]]+)\]/';

        return (string)preg_replace_callback($pattern, function (array $matches) use ($locale, &$stash): string {
            $token = 'WISDOMPH' . count($stash) . 'X';
            $stash[$token] = $this->processPlaceholders($matches[0], $locale);

            return $token;
        }, $markdown);
    }

    /**
     * Extracts YAML Front Matter and separates the raw text.
     *
     * @param string $content The raw content of the markdown file.
     * @return array Parsed front matter properties along with the 'raw_markdown' string.
     */
    private function extractFrontMatter(string $content): array
    {
        // Strip UTF-8 BOM
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;

        $data = ['raw_markdown' => trim($content)];

        if (preg_match('/\A---[ \t]*\R(?:(.*?)\R)?---[ \t]*(?:\R(.*))?\z/s', $content, $matches)) {
            $data['raw_markdown'] = trim($matches[2] ?? '');
            $lines = preg_split('/\R/', trim($matches[1] ?? '')) ?: [];

            foreach ($lines as $line) {
                if (preg_match('/^([a-zA-Z0-9_-]+)\s*:\s*(.*)$/', trim($line), $lineMatches)) {
                    $key = strtolower(trim($lineMatches[1]));
                    $value = trim($lineMatches[2], " \t\n\r\0\x0B\"'");

                    if ($key === 'raw_markdown') {
                        continue; // never let front matter overwrite the content
                    }

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
     * @return bool True if any modifications were made.
     */
    private function applyFallbacks(array &$data): bool
    {
        $updated = false;

        // Detect H1 with a colon and split it into H1 + H2
        $data['raw_markdown'] = preg_replace_callback(
            '/^#[ \t]+([^:\n]+):[ \t]*(.+)$/m',
            function (array $matches) use (&$data, &$updated) {
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
            },
            $data['raw_markdown']
        ) ?? $data['raw_markdown'];

        // Fallback for title
        if (empty($data['title'])) {
            if (preg_match('/^#[ \t]+(.+)$/m', $data['raw_markdown'], $titleMatches)) {
                $data['title'] = trim($titleMatches[1]);
                $updated = true;
            } else {
                $data['title'] = 'Unknown Wisdom';
            }
        }

        // Fallback for subtitle
        if (empty($data['subtitle']) && preg_match('/^##[ \t]+(.+)$/m', $data['raw_markdown'], $subMatches)) {
            $data['subtitle'] = trim($subMatches[1]);
            $updated = true;
        }

        return $updated;
    }
}