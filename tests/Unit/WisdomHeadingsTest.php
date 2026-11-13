<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Title and first heading of every wisdom file must not contain a colon.
 *
 * A "Title: Subtitle" in the front matter title or in the H1 splits into two parts. The page then shows
 * the subtitle twice (once from the split, once from the H2), as happened with a generated wisdom.
 * The title and the H1 are the main title only; the second part belongs in subtitle and H2.
 *
 * title and H1 are not required to be identical, many wisdoms use a longer title for search results.
 */
final class WisdomHeadingsTest extends TestCase
{
    private const WISDOMS_DIR = __DIR__ . '/../../public/wisdoms';

    private const LOCALE_DIRS = [
        'de' => self::WISDOMS_DIR,
        'en' => self::WISDOMS_DIR . '/en',
    ];

    /**
     * One test case per file and language, named like "en/NatureOfZero".
     *
     * @return iterable<string, array{string, string}>
     */
    public static function wisdomProvider(): iterable
    {
        foreach (self::LOCALE_DIRS as $locale => $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            foreach (glob($dir . '/*.md') ?: [] as $file) {
                $id = basename($file, '.md');
                yield $locale . '/' . $id => [$locale, $id];
            }
        }
    }

    #[DataProvider('wisdomProvider')]
    public function testTitleAndFirstHeadingHaveNoColon(string $locale, string $id): void
    {
        $label = $locale . '/' . $id;
        $content = (string)file_get_contents(self::LOCALE_DIRS[$locale] . '/' . $id . '.md');

        $this->assertSame(
            1,
            preg_match('/\A(?:\xEF\xBB\xBF)?---[ \t]*\R(.*?)\R---[ \t]*(?:\R|\z)(.*)\z/s', $content, $parts),
            "{$label} does not start with a front matter block."
        );

        if (preg_match('/^title:[ \t]*(.*)$/m', $parts[1], $titleMatch)) {
            $title = trim($titleMatch[1], " \t\"'");
            $this->assertStringNotContainsString(
                ':',
                $title,
                "The title of {$label} contains a colon. Keep only the main title, move the rest to 'subtitle'."
            );
        }

        if (preg_match('/^#[ \t]+(.+)$/m', $parts[2], $headingMatch)) {
            $this->assertStringNotContainsString(
                ':',
                $headingMatch[1],
                "The H1 of {$label} contains a colon. Keep only the main title and put the rest into the H2."
            );
        }
    }

    public function testWisdomFilesAreFound(): void
    {
        // Guards the data provider: without files the test above would pass without checking anything.
        $this->assertNotEmpty(iterator_to_array(self::wisdomProvider()), 'No wisdom files found.');
    }
}
