<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

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
    public function __construct(
        protected Aliases $aliases
    ) {
    }

    public function getImageHtml(string $id): string
    {
        $filename = $id . '.jpg';
        $url = 'https://media.guru-wisdom.de/images/' . $filename;
        
        return '
          <picture>
            <img src="' . $url . '" alt="Wisdom Image" class="img-fluid">
          </picture>
        ';
    }

    public function getAudioHtml(string $id): string
    {
        $url = 'https://media.guru-wisdom.de/audio/' . $id . '.mp3';
        
        return '
          <audio controls autoplay>
             <source src="' . $url . '" type="audio/mpeg">
             </audio>';
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

        return [$previousId, $currentId, $nextId];
    }

    /**
     * Processes custom placeholders in the markdown and replaces them with HTML.
     */
    public function processPlaceholders(string $text): string
    {
        // 1. YouTube Placeholders
        $text = preg_replace_callback('/\[youtube:([a-zA-Z0-9_-]+)\]/', function(array $matches) {
            $videoId = $matches[1]; 
            
            // htmlspecialchars replaces the old yii\bootstrap5\Html::encode
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

        // 2. Future Placeholders (e.g. Vimeo, Audio, Galleries)
        // $text = preg_replace_callback('/\[vimeo:([0-9]+)\]/', ...
        
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
    public function parseFile(string $id, bool $autoSave = true): array
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
        $isDevMode = false; // Adjust this according to your Yii3 environment logic
        if ($autoSave && $needsUpdate && $isDevMode) {
            $this->updateFile($filePath, $data);
        }

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