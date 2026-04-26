<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use App\Service\GuruWisdomService;
use Yiisoft\Aliases\Aliases;

/**
 * Unit tests for the GuruWisdomService class.
 */
final class GuruWisdomServiceTest extends TestCase
{
    private GuruWisdomService $guruWisdom;
    
    // 1. We now use the real Aliases class, no longer a mock
    private Aliases $aliases; 
    private string $tempDir;

    /**
     * Executed before EACH test.
     * Here we build a temporary file system to simulate reading markdown files.
     */
    protected function setUp(): void
    {
        // 1. Create a temporary directory for dummy wisdom files
        $this->tempDir = sys_get_temp_dir() . '/wisdom_tests_' . uniqid('', true);
        mkdir($this->tempDir);

        // 2. Create two dummy files with different dates (for testing sorting)
        $file1Content = "---\ntitle: First Wisdom\ndate: 2026-01-01\n---\n\n# The first H1\nAnd a [youtube:12345] video.";
        $file2Content = "---\ntitle: Second Wisdom\ndate: 2026-02-01\n---\n\n# The second H1\nSimple text.";
        
        file_put_contents($this->tempDir . '/first-wisdom.md', $file1Content);
        file_put_contents($this->tempDir . '/second-wisdom.md', $file2Content);

        // 3. Feed the REAL Aliases instance with our test directory
        $this->aliases = new Aliases([
            '@public/wisdoms' => $this->tempDir
        ]);

        // 4. Instantiate the class with the real Aliases service
        $this->guruWisdom = new GuruWisdomService($this->aliases);
    }

    // ... The rest (tearDown and the test... methods) remains exactly the same!

    /**
     * Executed after EACH test.
     * Cleans up our temporary files.
     */
    protected function tearDown(): void
    {
        $files = glob($this->tempDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($this->tempDir);
    }

    // --- TEST CASES ---

    public function testGetFilePathReturnsCorrectPath(): void
    {
        $path = $this->guruWisdom->getFilePath('first-wisdom');
        $this->assertEquals($this->tempDir . '/first-wisdom.md', $path);
    }

    public function testSanitizeIdReturnsExactIdIfExists(): void
    {
        $id = $this->guruWisdom->sanitizeId('first-wisdom');
        $this->assertEquals('first-wisdom', $id);
    }

    public function testSanitizeIdFallsBackToRandomIdIfNotFound(): void
    {
        $id = $this->guruWisdom->sanitizeId('does-not-exist');
        // Since 'does-not-exist' is missing, the function must pick 'first-wisdom' or 'second-wisdom'
        $this->assertContains($id, ['first-wisdom', 'second-wisdom']);
    }

    public function testGetSortedWisdomIdsSortsByDateDescending(): void
    {
        // "second-wisdom" has the date 2026-02-01, "first-wisdom" has 2026-01-01
        $sortedIds = $this->guruWisdom->getSortedWisdomIds();
        
        $this->assertCount(2, $sortedIds);
        $this->assertEquals('second-wisdom', $sortedIds[0]); // Newest first
        $this->assertEquals('first-wisdom', $sortedIds[1]);
    }

    public function testGetNavigationIdsReturnsCorrectNeighbors(): void
    {
        // Since the sorting is ['second-wisdom', 'first-wisdom']:
        $nav = $this->guruWisdom->getNavigationIds('first-wisdom');
        
        $this->assertEquals('second-wisdom', $nav['prev']);
        $this->assertEquals('first-wisdom', $nav['current']);
        $this->assertNull($nav['next']); // 'first-wisdom' is the oldest item
    }

    public function testProcessPlaceholdersConvertsYouTubeTags(): void
    {
        $rawText = "Check this out: [youtube:dQw4w9WgXcQ]";
        $html = $this->guruWisdom->processPlaceholders($rawText);
        
        $this->assertStringContainsString('<iframe', $html);
        $this->assertStringContainsString('https://www.youtube.com/embed/dQw4w9WgXcQ', $html);
    }

    public function testProcessPlaceholdersConvertsSpotifyTags(): void
    {
        $rawText = "Listen to this: [spotify:track:4uLU6hMCjM]";
        $html = $this->guruWisdom->processPlaceholders($rawText);
        
        $this->assertStringContainsString('<iframe', $html);
        $this->assertStringContainsString('spotify.com', $html); 
    }

    public function testParseFileExtractsFrontMatterAndParsesMarkdown(): void
    {
        $data = $this->guruWisdom->parseFile('first-wisdom');

        // Check if front matter was read correctly
        $this->assertArrayHasKey('title', $data);
        $this->assertEquals('First Wisdom', $data['title']);

        // Check if markdown was rendered to HTML (H1 tag)
        $this->assertArrayHasKey('htmloutput', $data);
        $this->assertStringContainsString('<h1>The first H1</h1>', $data['htmloutput']);

        // Check if placeholders were replaced during parsing
        $this->assertStringContainsString('<iframe', $data['htmloutput']);

        // Check if 'raw_markdown' was cleanly removed at the end
        $this->assertArrayNotHasKey('raw_markdown', $data);
    }
}