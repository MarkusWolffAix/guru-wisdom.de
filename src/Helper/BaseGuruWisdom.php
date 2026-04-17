<?php

declare(strict_types=1);

namespace App\Helper;

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
class BaseGuruWisdom
{
    /**
     * Constructor injects required Yii3 dependencies.
     */
    protected Aliases $aliases;

    public function __construct(
        Aliases $aliases 
    ) {
        $this->aliases = $aliases; 
    }
    public  function getImageHtml($id)
    {
       $url = "https://media.guru-wisdom.de/images/".$id.".jpg ";

       $headers = @get_headers($url);
       $htmlcode="";
       if (!$headers || strpos($headers[0], '200') === false) {
            return ""; 
       };
    
        $htmlcode=$htmlcode.'<picture><img src="'.$url.'" alt="" class="img-fluid"></picture>';
        
        return $htmlcode;
    }

    public  function getAudioHtml($id)
    {
        $url = "https://media.guru-wisdom.de/audio/".$id.".mp3";

        $headers = @get_headers($url);
        $htmlcode="";

        if (!$headers || strpos($headers[0], '200') === false) {
            return ""; 
        };

        $htmlcode = '<audio controls> <source src="'.$url.'" type="audio/mpeg"> </audio>';

    return $htmlcode;
    }


    /**
     * Retrieves the previous, current, and next IDs for navigation.
     */
    public function getNavigationIds(?string $id = null): array
    {
        $path = $this->aliases->get('@public/wisdoms/');
        $files = glob($path . '*.md');
        $count = count($files);

        if ($count === 0) {
            return ['', '', ''];
        }

        if ($id !== null) {
            $searchPath = $path . $id . '.md';
            $currentIndex = array_search($searchPath, $files);
            
            // Fallback to the first file if the ID was not found
            if ($currentIndex === false) {
                $currentIndex = 0;
            }
        } else {
            $currentIndex = array_rand($files);
        }

        $currentFile = $files[$currentIndex];

        // Calculate previous and next files using wrap-around logic
        $previousIndex = ($currentIndex - 1 + $count) % $count;
        $previousFile = $files[$previousIndex];

        $nextIndex = ($currentIndex + 1) % $count;
        $nextFile = $files[$nextIndex];

        $currentId = basename($currentFile, '.md');
        $nextId = basename($nextFile, '.md');
        $previousId = basename($previousFile, '.md');

        return ["prev" => $previousId, "current" => $currentId, "next" => $nextId];
    }

/**
     * Processes custom placeholders in a string and converts them into HTML components.
     * * Examples:
     * - [youtube:dQw4w9WgXcQ] -> Standard YouTube Embed
     * - [spotify:track:4uLU6hMCjMI75M1A2tKUQC] -> Spotify Player for a specific track
     * - [image:https://example.com/photo.jpg|A beautiful sunset] -> Responsive image with alt text
     *
     * @param string $text The raw text containing placeholders.
     * @return string The processed text with HTML tags.
     */
    public function processPlaceholders(string $text): string
    {
        // 1. YouTube Placeholders
        // Matches [youtube:VIDEO_ID]
        $text = preg_replace_callback('/\[youtube:([a-zA-Z0-9_-]+)\]/', function(array $matches) {
            $videoId = $matches[1]; 
            
            return '<div class="ratio ratio-16x9 my-4" style="max-width: 640px;">
              <iframe 
                  src="https://www.youtube.com/embed/' . htmlspecialchars($videoId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" 
                  title="YouTube video" 
                  frameborder="0" 
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                  allowfullscreen>
              </iframe>
          </div>';
        }, $text);

        // 2. Spotify Placeholders
        // Matches [spotify:type:ID] where type is track, album, playlist, artist, episode, or show
        $text = preg_replace_callback('/\[spotify:(track|album|playlist|artist|episode|show):([a-zA-Z0-9]+)\]/', function(array $matches) {
            $type = $matches[1];
            $spotifyId = $matches[2];
            
            $safeType = htmlspecialchars($type, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeId = htmlspecialchars($spotifyId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            return '<div class="my-4" style="max-width: 640px;">
                <iframe style="border-radius:12px" 
                    src="https://open.spotify.com/embed/' . $safeType . '/' . $safeId . '?utm_source=generator" 
                    width="100%" 
                    height="352" 
                    frameBorder="0" 
                    allowfullscreen="" 
                    allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" 
                    loading="lazy">
                </iframe>
            </div>';
        }, $text);

        // 3. Image Placeholders
        // Matches [image:URL] or [image:URL|ALT_TEXT]
            $text = preg_replace_callback('/\[image:([^\|\]]+)(?:\|([^\]]+))?\]/', function(array $matches) {
            $imageUrl = "https://media.guru-wisdom.de/images/" . trim($matches[1].".jpg"); // Assuming .jpg extension for all images
            // Check if an optional alt text was provided after the pipe symbol
            $altText = isset($matches[2]) ? trim($matches[2]) : '';
            
            $safeUrl = htmlspecialchars($imageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeAlt = htmlspecialchars($altText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            // Returns a responsive image using Bootstrap-like classes
            return '<img src="' . $safeUrl . '" alt="' . $safeAlt . '" class="img-fluid my-4" style="max-width: 100%; height: auto; border-radius: 8px;">';
        }, $text);
        
        return $text;
    }

    public function getFilePath(string $id): string 
    {
        return $this->aliases->get('@public/wisdoms/' . $id . '.md');
    }

    /**
     * The main orchestrator function.
     * Parses the markdown file, handles fallbacks, and renders HTML.
     */
    public function parseFile(string $id, bool $autoSave = false): array
    {
        $filePath = $this->getFilePath($id);


        
        if (!file_exists($filePath)) {
            return ['htmloutput' => ''];
        }

        $content = file_get_contents($filePath);
        
        // Step 1: Extract front matter and raw markdown text
        $data = $this->extractFrontMatter($content);
        
        // Step 2: Apply fallbacks and split colon-headings
        $needsUpdate = $this->applyFallbacks($data);

        // Step 3: Auto-save the raw markdown if needed
        // $isDevMode = false; // Adjust this according to your Yii3 environment logic
        // if ($autoSave && $needsUpdate && $isDevMode) {
        //     $this->updateFile($filePath, $data);
        // }

        // Step 4: Process placeholders and convert raw markdown to final HTML
        $contentForParsing = $this->processPlaceholders($data['raw_markdown']);
        $parser = new GithubMarkdown(); 
        
        $data['htmloutput'] = $parser->parse($contentForParsing);

        // Remove the raw markdown from the output array as the view only needs HTML
        unset($data['raw_markdown']);

        return $data;
    }

    /**
     * Extracts YAML Front Matter and cleans the text.
     */
    private function extractFrontMatter(string $content): array
    {
        // We temporarily use 'raw_markdown' for internal processing
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
     */
    private function updateFile(string $filePath, array $data): void
    {
        $newContent = "---\n";
        
        foreach ($data as $key => $value) {
            // Skip the raw markdown and empty values for the front matter header
            if ($key === 'raw_markdown' || empty($value)) {
                continue;
            }
            
            if (is_array($value)) {
                $newContent .= $key . ': [' . implode(', ', $value) . "]\n";
            } else {
                $newContent .= $key . ': ' . $value . "\n";
            }
        }
        
        // Append the clean, raw markdown at the end of the file
        $newContent .= "---\n\n" . $data['raw_markdown'];
        
        file_put_contents($filePath, $newContent);
    }
}