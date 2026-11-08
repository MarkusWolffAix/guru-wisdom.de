<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * The category keys in the front matter are shown with a label from resources/messages.
 * Both languages must define the same category keys, otherwise one language shows a raw key.
 */
final class CategoryMessagesTest extends TestCase
{
    private const MESSAGES_DIR = __DIR__ . '/../../resources/messages';

    /**
     * @return array<string, string> The category messages of a language, key => label.
     */
    private function categories(string $locale): array
    {
        return require self::MESSAGES_DIR . '/' . $locale . '/categories.php';
    }

    public function testGermanAndEnglishDefineTheSameCategories(): void
    {
        $de = array_keys($this->categories('de'));
        $en = array_keys($this->categories('en'));
        sort($de);
        sort($en);

        $this->assertNotEmpty($de, 'No category messages found for "de".');
        $this->assertSame($de, $en, 'resources/messages/de/categories.php and en/categories.php must define the same keys.');
    }

    public function testCategoryLabelsAreNotEmpty(): void
    {
        foreach (['de', 'en'] as $locale) {
            foreach ($this->categories($locale) as $key => $label) {
                $this->assertNotSame('', trim((string)$label), "Empty label for category {$key} ({$locale}).");
            }
        }
    }
}
