<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class WisdomFrontmatterTest extends TestCase
{
    /**
     * Path to the folder containing the Markdown files.
     * Adjust this path to match your Yii3 project structure.
     */
    private const WISDOMS_DIR = __DIR__ . '/../../public/wisdoms';

    /**
     * The DataProvider scans the directory and passes each 
     * found .md file individually to the test method.
     */
    public static function markdownFileProvider(): iterable
    {
        if (!is_dir(self::WISDOMS_DIR)) {
            self::markTestSkipped('The directory ' . self::WISDOMS_DIR . ' does not exist.');
            return [];
        }

        foreach (glob(self::WISDOMS_DIR . '/*.md') as $file) {
            // The filename is used as the key, so PHPUnit names the test in the terminal (e.g., "NatureOfZero.md")
            yield basename($file) => [$file];
        }
    }

    /**
     * @dataProvider markdownFileProvider
     */
    public function testFrontmatterStructure(string $filePath): void
    {
        $content = file_get_contents($filePath);

        // 1. Check if a frontmatter block (--- ... ---) exists at all
        $pattern = '/^---\s*(.*?)\s*---/s';
        $hasFrontmatter = preg_match($pattern, $content, $matches);
        
        $this->assertSame(
            1, 
            $hasFrontmatter, 
            "The file " . basename($filePath) . " does not have a valid YAML frontmatter block at the beginning."
        );

        $frontmatterYaml = $matches[1];

        // 2. Parse YAML
        // Catch exception if the YAML is broken (e.g., wrong indentation)
        try {
            $data = Yaml::parse($frontmatterYaml);
        } catch (\Exception $e) {
            $this->fail("YAML syntax error in " . basename($filePath) . ": " . $e->getMessage());
        }

        $this->assertIsArray($data, "The frontmatter in " . basename($filePath) . " could not be parsed as an array.");

        // 3. Check required fields
        $expectedKeys = ['id', 'title', 'subtitle', 'description', 'date', 'author', 'tags', 'categories'];
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey(
                $key, 
                $data, 
                "Missing key '{$key}' in the frontmatter of " . basename($filePath)
            );
        }

        // 4. Validate data types and specific content
        $this->assertIsString($data['id'], "'id' must be a string.");
        $this->assertIsString($data['title'], "'title' must be a string.");
        $this->assertIsString($data['subtitle'], "'subtitle' must be a string.");
        $this->assertIsString($data['description'], "'description' must be a string.");

        // Date: YAML parsers often convert a date (YYYY-MM-DD) without quotes into integers/timestamps.
        // We dynamically check if a value exists (typing is secondary here as long as it's valid).
        $this->assertNotEmpty($data['date'], "'date' must not be empty.");

        // Fixed author
        $this->assertEquals(
            "Markus Wolff guru-wisdom.de", 
            $data['author'], 
            "The author in " . basename($filePath) . " deviates from the standard format."
        );

        // Arrays (Tags & Categories)
        $this->assertIsArray($data['tags'], "'tags' must be a list (array) (e.g., [\"Tag1\", \"Tag2\"]).");
        $this->assertIsArray($data['categories'], "'categories' must be a list (array).");
        
        // Check if arrays are not empty (optional)
        $this->assertNotEmpty($data['tags'], "At least one tag must be provided.");
        $this->assertNotEmpty($data['categories'], "At least one category must be provided.");
    }
}