<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Service\GuruWisdomService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yiisoft\Aliases\Aliases;

/**
 * Checks the front matter of every wisdom the way the application reads it.
 *
 * The German file is the base. A translation only has to contain what differs, so the checks run
 * on the merged front matter (GuruWisdomService::getFrontMatter), not on the raw file.
 */
final class WisdomFrontmatterTest extends TestCase
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
    public function testFrontmatterStructure(string $locale, string $id): void
    {
        $label = $locale . '/' . $id;
        $file = self::LOCALE_DIRS[$locale] . '/' . $id . '.md';

        $this->assertTrue(
            (new GuruWisdomService(new Aliases(['@public/wisdoms' => self::WISDOMS_DIR])))->isValidId($id),
            "The file name of {$label} contains characters that are not allowed in an ID."
        );

        $content = (string)file_get_contents($file);
        $this->assertSame(
            1,
            preg_match('/\A(?:\xEF\xBB\xBF)?---[ \t]*\R(.*?)\R---[ \t]*(?:\R|\z)/s', $content, $matches),
            "{$label} does not start with a front matter block."
        );

        // The ID is the file name. A front matter 'id' is not needed; if present it must not deviate.
        if (preg_match('/^id:[ \t]*(.*)$/m', $matches[1], $idMatch)) {
            $this->assertSame(
                $id,
                trim($idMatch[1], " \t\"'"),
                "The front matter 'id' of {$label} differs from the file name. Remove the 'id' line."
            );
        }

        $service = new GuruWisdomService(new Aliases(['@public/wisdoms' => self::WISDOMS_DIR]));
        $data = $service->getFrontMatter($id, $locale);

        foreach (['title', 'subtitle', 'description', 'date', 'author', 'tags', 'categories'] as $key) {
            $this->assertArrayHasKey($key, $data, "Missing '{$key}' in {$label}.");
            $this->assertNotEmpty($data[$key], "'{$key}' is empty in {$label}.");
        }

        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}$/',
            (string)$data['date'],
            "'date' of {$label} must have the form YYYY-MM-DD (with leading zeros)."
        );
        [$year, $month, $day] = array_map('intval', explode('-', (string)$data['date']));
        $this->assertTrue(checkdate($month, $day, $year), "'date' of {$label} is not a valid date.");

        $this->assertSame(
            'Markus Wolff guru-wisdom.de',
            $data['author'],
            "The author of {$label} deviates from the standard."
        );

        foreach (['tags', 'categories'] as $listKey) {
            $this->assertIsArray($data[$listKey], "'{$listKey}' of {$label} must be a list, e.g. [\"A\", \"B\"].");
            foreach ($data[$listKey] as $item) {
                $this->assertMatchesRegularExpression(
                    '/^"[^"]+"$/',
                    (string)$item,
                    "Entry {$item} in '{$listKey}' of {$label} is not a complete quoted string (missing quote or comma inside)."
                );
            }
        }
    }
}
