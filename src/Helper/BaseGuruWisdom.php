<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace app\helpers;

use Yii;
use yii\base\InvalidArgumentException;
use cebe\markdown\GithubMarkdown;
use yii\helpers\FileHelper;
use yii\helpers\ArrayHelper;
use yii\bootstrap5\Html;

/**
 * BaseGuruWisdomP
 *
 * Do not use BaseGuruWisdom

 *
 * @author Markus Wolff <markus.wolff@guru-wisdom.com>
 * @since 2026-02-13
 */
class BaseGuruWisdom
{

 
    public static function getIMGCode($id)
    {
      $htmlcode='';
      $ext=".jpg";
      $filename=$id.$ext;
      $url="https://media.guru-wisdom.de/images/".$filename;
      $htmlcode=$htmlcode.'
          <picture>
            <img src="'.$url.'" alt="" class="img-fluid">
          </picture>
        ';
        
      return $htmlcode;
    }

    public static function getAudioCode($id)
    {
          $url="https://media.guru-wisdom.de/audio/".$id.".mp3";
          $htmlcode='
          <audio controls autoplay >
             <source src="'.$url.'" type="audio/mpeg">
             <!-- Your browser does not support the audio element. -->
          </audio>  ';

          return $htmlcode;
    }


    public static function getPrevCurNextId($id){
      $files = FileHelper::findFiles(Yii::getAlias("@webroot/wisdoms/"), [
        'only' => ['*.md'],
       'recursive' => false,
        ]);
      $count = count($files);


    if ($count > 0) {
    // 3. Determine a random index
    if (isset($id)) {
      $currentIndex = array_search(Yii::getAlias("@webroot/wisdoms/".$id.".md"), $files);
    } else {
      $currentIndex = array_rand($files);
    }
    $currentFile = $files[$currentIndex];

    // 4. Calculate previous and next files (Wrap-Around Logic)
    // The modulo operator (%) ensures we always stay within the array bounds.
    
    // Previous: We add $count to avoid negative numbers before applying modulo.
    $previousIndex = ($currentIndex - 1 + $count) % $count;
    $previousFile = $files[$previousIndex];

    // Next: We add 1 and apply modulo of the total count.
    $nextIndex = ($currentIndex + 1) % $count;
    $nextFile = $files[$nextIndex];


    $curId =  basename($currentFile,'.md');
    $nextId = basename($nextFile,'.md');
    $prevId = basename($previousFile,'.md');

    return [$prevId, $curId, $nextId ];

}


    }

    public static function placeholder2html($text)
    {
          $text = preg_replace_callback('/\[youtube:([a-zA-Z0-9_-]+)\]/', function($treffer) {
          $videoId = $treffer[1]; // Das ist die extrahierte ID (z.B. dQw4w9WgXcQ)
          // Wir holen uns das original YouTube-Vorschaubild in höchster Qualität
          $thumbnailUrl = "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";
          return '<div class="ratio ratio-16x9 my-4" style="max-width: 640px;">
            <iframe 
                src="https://www.youtube.com/embed/' . Html::encode($videoId) . '" 
                title="YouTube video" 
                frameborder="0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                allowfullscreen>
            </iframe>
        </div>';
          }, $text);
            
      return $text;
    }

    

    public static function DebugVar($varToDebug)
    {
      // 
     if (YII_DEBUG) {
         \yii\helpers\VarDumper::dump("DEBUG: $varToDebug", 10, true);
      }
     // VarDumper::dump($varToDebug, 10, true);
    }


/**
     * 1. The main function (The "Orchestrator")
     */
    public static function parseFile($id, $autoSave = true)
    {
        $filePath=Yii::getAlias("@webroot/wisdoms/".$id.".md");

        if (!file_exists($filePath)) {
            return ['htmloutput' => ''];
        }

        $content = file_get_contents($filePath);
        
        // Step 1: Extract front matter and RAW markdown text
        $data = self::extractFrontMatter($content);
        
        // Step 2: Apply fallbacks and split colon-headings (on the RAW markdown)
        $needsUpdate = self::applyFallbacks($data);

        // Step 3: Auto-save the RAW markdown if needed (Dev mode only)
        $isDevMode = defined('YII_ENV_DEV') && YII_ENV_DEV;
        $isDevMode= false; 
        if ($autoSave && $needsUpdate && $isDevMode) {
            self::updateFile($filePath, $data);
        }

        // --- DEIN NEUER CODE KOMMT HIERHIN! ---
        // Step 4: Process placeholders and convert raw markdown to final HTML
        $contentForParsing = self::placeholder2html($data['raw_markdown']);
        $parser = new \cebe\markdown\GithubMarkdown(); // Yii2 Standard-Parser
        
        // Speichere das finale HTML in 'htmloutput'
        $data['htmloutput'] = $parser->parse($contentForParsing);

        // Optional: Das rohe Markdown aus dem Array löschen, da der View nur HTML braucht
        unset($data['raw_markdown']);

        return $data;
    }

    /**
     * 2. Extracts YAML Front Matter and cleans the text.
     */
    private static function extractFrontMatter($content)
    {
        // Wir nutzen vorübergehend 'raw_markdown' für die interne Verarbeitung
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
     * 3. Handles missing titles and splits H1 headings containing a colon.
     */
    private static function applyFallbacks(&$data)
    {
        $updated = false;

        // Detect H1 with a colon and rewrite the RAW markdown structure
        $data['raw_markdown'] = preg_replace_callback('/^#\s+([^:\n]+):\s*(.+)$/m', function($matches) use (&$data, &$updated) {
            $mainTitle = trim($matches[1]);
            $subTitle = trim($matches[2]);

            if (empty($data['title'])) {
                $data['title'] = $mainTitle;
            }
            if (empty($data['subtitle'])) {
                $data['subtitle'] = $subTitle;
            }
            
            $updated = true; 
            
            return "# " . $mainTitle . "\n## " . $subTitle;
            
        }, $data['raw_markdown'], -1, $count);

        if ($count > 0) {
            $updated = true;
        }

        // Standard Fallback for Title
        if (empty($data['title'])) {
            if (preg_match('/^#\s+(.+)$/m', $data['raw_markdown'], $titleMatches)) {
                $data['title'] = trim($titleMatches[1]);
                $updated = true;
            } else {
                $data['title'] = 'Unknown Wisdom';
            }
        }

        // Standard Fallback for Subtitle
        if (empty($data['subtitle']) && preg_match('/^##\s+(.+)$/m', $data['raw_markdown'], $subMatches)) {
            $data['subtitle'] = trim($subMatches[1]);
            $updated = true;
        }

        return $updated;
    }

    /**
     * 4. Writes the updated data back into the physical file.
     */
    private static function updateFile($filePath, $data)
    {
        $newContent = "---\n";
        
        foreach ($data as $key => $value) {
            // Wir überspringen das rohe Markdown und leere Werte für den Header
            if ($key === 'raw_markdown' || empty($value)) {
                continue;
            }
            
            if (is_array($value)) {
                $newContent .= $key . ": [" . implode(', ', $value) . "]\n";
            } else {
                $newContent .= $key . ": " . $value . "\n";
            }
        }
        
        // Wir hängen am Ende das saubere, rohe Markdown wieder an
        $newContent .= "---\n\n" . $data['raw_markdown'];
        
        file_put_contents($filePath, $newContent);
    }

}   
