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
        foreach (['/en', ''] as $subDir) {
            $files = glob($this->tempDir . $subDir . '/*') ?: [];
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            if ($subDir !== '' && is_dir($this->tempDir . $subDir)) {
                rmdir($this->tempDir . $subDir);
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
        
        // Korrektur: Wir suchen in $html, nicht in $result!
        $this->assertStringContainsString('class="two-click-container', $html);
        $this->assertStringContainsString('data-type="youtube"', $html);
        $this->assertStringContainsString('data-src="https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ"', $html);
    }

    public function testProcessPlaceholdersConvertsSpotifyTags(): void
    {
        $rawText = "Listen to this: [spotify:track:4uLU6hMCjM]";
        $html = $this->guruWisdom->processPlaceholders($rawText);
        
        // Suchen nach dem Zwei-Klick-Container statt nach iframe
        $this->assertStringContainsString('class="two-click-container', $html);
        $this->assertStringContainsString('data-type="spotify"', $html);
        $this->assertStringContainsString('data-src="https://open.spotify.com/embed/track/4uLU6hMCjM?utm_source=generator"', $html); 
    }

    public function testProcessPlaceholdersConvertsHtmlTagToIframe(): void
    {
        $html = $this->guruWisdom->processPlaceholders('[html:UranusClockwork|Das Uranus-Uhrwerk|640]');

        $this->assertStringContainsString('<figure class="html-embed', $html);
        $this->assertStringContainsString('<iframe src="/html/UranusClockwork.html?lang=de"', $html);
        $this->assertStringContainsString('title="Das Uranus-Uhrwerk"', $html);
        $this->assertStringContainsString('height: 640px', $html);
        $this->assertStringContainsString('loading="lazy"', $html);
        $this->assertStringContainsString('data-autoheight="true"', $html);
        $this->assertStringContainsString('Im Vollbild öffnen', $html);
    }

    public function testProcessPlaceholdersHtmlUsesEnglishPageAndTexts(): void
    {
        $html = $this->guruWisdom->processPlaceholders('[html:UranusClockwork|The Uranus Clockwork]', 'en');

        $this->assertStringContainsString('src="/html/UranusClockwork.html?lang=en"', $html);
        $this->assertStringContainsString('Open full screen', $html);
    }

    public function testProcessPlaceholdersHtmlDefaultsForTitleAndHeight(): void
    {
        $html = $this->guruWisdom->processPlaceholders('[html:UranusClockwork]');

        $this->assertStringContainsString('title="UranusClockwork"', $html);
        $this->assertStringContainsString('height: 720px', $html);
    }

    public function testProcessPlaceholdersHtmlClampsHeight(): void
    {
        $low = $this->guruWisdom->processPlaceholders('[html:Demo|Demo|50]');
        $high = $this->guruWisdom->processPlaceholders('[html:Demo|Demo|9999]');

        $this->assertStringContainsString('height: 200px', $low);
        $this->assertStringContainsString('height: 3000px', $high);
    }

    public function testProcessPlaceholdersHtmlEscapesTitle(): void
    {
        $html = $this->guruWisdom->processPlaceholders('[html:Demo|"><script>alert(1)</script>]');

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('title="&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;"', $html);
    }

    public function testProcessPlaceholdersHtmlRejectsPaths(): void
    {
        $text = '[html:../secret] [html:a/b] [html:x.html]';

        $this->assertSame($text, $this->guruWisdom->processPlaceholders($text));
    }

    public function testParseFileRendersHtmlEmbedOutsideParagraph(): void
    {
        file_put_contents(
            $this->tempDir . '/html-wisdom.md',
            "---\ntitle: Html page\ndate: 2026-03-01\n---\n\n# Html page\n\nText before.\n\n[html:My_Sim-2|A *bold* _title_]\n\nText after."
        );

        $html = $this->guruWisdom->parseFile('html-wisdom')['htmloutput'];

        $this->assertStringContainsString('src="/html/My_Sim-2.html?lang=de"', $html);
        $this->assertStringContainsString('title="A *bold* _title_"', $html);
        $this->assertStringNotContainsString('<p><figure', $html);
        $this->assertStringNotContainsString('<em>', $html);
    }

    public function testParseFileKeepsMathFormulasUnchanged(): void
    {
        // Nowdoc: "$" must not be interpolated by PHP
        $markdown = <<<'MD'
            ---
            title: Math
            date: 2026-03-02
            ---

            # Math

            Fibonacci ($F_n = F_{n-1} + F_{n-2}$) and *real* italics.

            $$|\Psi_{A}\rangle = a_i b_i$$

            Display \[ x_1 < y_2 \] and inline \( a_b \).
            MD;
        file_put_contents($this->tempDir . '/math-wisdom.md', $markdown . "\n");

        $html = $this->guruWisdom->parseFile('math-wisdom')['htmloutput'];

        $this->assertStringContainsString('($F_n = F_{n-1} + F_{n-2}$)', $html);
        $this->assertStringContainsString('<em>real</em>', $html);
        $this->assertStringContainsString('$$|\Psi_{A}\rangle = a_i b_i$$', $html);
        $this->assertStringContainsString('\[ x_1 &lt; y_2 \]', $html);
        $this->assertStringContainsString('\( a_b \)', $html);
        $this->assertStringNotContainsString('<em>n', $html);
        $this->assertStringNotContainsString('WISDOMMATH', $html);
    }

    public function testProtectMathLeavesCodeUntouched(): void
    {
        $text = <<<'MD'
            Cost `$HOME and $PATH` and $x_1$

            ```
            echo "$a $b"
            ```
            MD;
        $stash = [];
        $result = GuruWisdomService::protectMath($text, $stash);

        $this->assertStringContainsString('`$HOME and $PATH`', $result);
        $this->assertStringContainsString('echo "$a $b"', $result);
        $this->assertSame(['WISDOMMATH0X' => '$x_1$'], $stash);
    }

    public function testParseFileMathAndPlaceholdersTogether(): void
    {
        $markdown = <<<'MD'
            ---
            title: Mixed
            date: 2026-03-03
            ---

            # Mixed

            [image:My_Pic_2|Alt]

            Value $a_1$ here.
            MD;
        file_put_contents($this->tempDir . '/mixed-wisdom.md', $markdown . "\n");

        $html = $this->guruWisdom->parseFile('mixed-wisdom')['htmloutput'];

        $this->assertStringContainsString('My_Pic_2.webp', $html);
        $this->assertStringContainsString('Value $a_1$ here.', $html);
    }

    public function testParseFileExtractsFrontMatterAndParsesMarkdown(): void
    {
        $data = $this->guruWisdom->parseFile('first-wisdom');

        $this->assertArrayHasKey('title', $data);
        $this->assertEquals('First Wisdom', $data['title']);
        $this->assertArrayHasKey('htmloutput', $data);
        $this->assertStringContainsString('<h1>The first H1</h1>', $data['htmloutput']);

        // Suchen nach dem Zwei-Klick-Container statt nach iframe
        $this->assertStringContainsString('class="two-click-container', $data['htmloutput']);
        $this->assertStringContainsString('data-type="youtube"', $data['htmloutput']);
        $this->assertStringContainsString('data-src="https://www.youtube-nocookie.com/embed/12345"', $data['htmloutput']);

        $this->assertArrayNotHasKey('raw_markdown', $data);
    }

    // --- FRONT MATTER INHERITANCE (translations only contain what differs) ---

    private function writeWisdom(string $id, string $content, string $locale = 'de'): void
    {
        $dir = $locale === 'de' ? $this->tempDir : $this->tempDir . '/' . $locale;
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        file_put_contents($dir . '/' . $id . '.md', $content);
    }

    public function testGetFrontMatterInheritsFromDefaultLanguage(): void
    {
        $this->writeWisdom('pair', "---\ntitle: Titel\nauthor: Markus\ndate: 2026-03-01\ntags: [\"a\", \"b\"]\n---\n\n# Titel\nText.");
        $this->writeWisdom('pair', "---\ntitle: Title\nsubtitle: Sub\n---\n\n# Title\nText.", 'en');

        $data = $this->guruWisdom->getFrontMatter('pair', 'en');

        $this->assertSame('Title', $data['title']);
        $this->assertSame('Sub', $data['subtitle']);
        $this->assertSame('Markus', $data['author']);
        $this->assertSame('2026-03-01', $data['date']);
        $this->assertSame(['"a"', '"b"'], $data['tags']);
        $this->assertSame('pair', $data['id']);
        $this->assertArrayNotHasKey('raw_markdown', $data);
    }

    public function testGetFrontMatterLocaleOverridesAndDoesNotInheritTexts(): void
    {
        $this->writeWisdom('pair', "---\ntitle: Titel\nsubtitle: Untertitel\ndescription: Beschreibung\ntags: [\"a\"]\n---\n\n# Titel");
        $this->writeWisdom('pair', "---\ntitle: Title\ntags: [\"x\", \"y\"]\n---\n\n# Title", 'en');

        $data = $this->guruWisdom->getFrontMatter('pair', 'en');

        $this->assertSame(['"x"', '"y"'], $data['tags'], 'A key in the locale file overrides the default.');
        $this->assertArrayNotHasKey('subtitle', $data, 'Subtitle is language specific and not inherited.');
        $this->assertArrayNotHasKey('description', $data, 'Description is language specific and not inherited.');
    }

    public function testGetFrontMatterIgnoresIdFromFile(): void
    {
        $this->writeWisdom('real-name', "---\nid: old-name\ntitle: T\n---\n\n# T");

        $this->assertSame('real-name', $this->guruWisdom->getFrontMatter('real-name')['id']);
    }

    public function testGetFrontMatterWithoutDefaultFileKeepsOwnData(): void
    {
        $this->writeWisdom('only-en', "---\ntitle: Only\nauthor: Me\n---\n\n# Only", 'en');

        $data = $this->guruWisdom->getFrontMatter('only-en', 'en');

        $this->assertSame('Me', $data['author']);
        $this->assertSame('only-en', $data['id']);
    }

    public function testGetFrontMatterReturnsEmptyForUnknownOrInvalidId(): void
    {
        $this->assertSame([], $this->guruWisdom->getFrontMatter('does-not-exist'));
        $this->assertSame([], $this->guruWisdom->getFrontMatter('../etc/passwd'));
    }

    public function testParseFileUsesInheritedFrontMatter(): void
    {
        $this->writeWisdom('pair', "---\ntitle: Titel\nauthor: Markus\n---\n\n# Titel");
        $this->writeWisdom('pair', "---\ntitle: Title\n---\n\n# Title\nHello.", 'en');

        $data = $this->guruWisdom->parseFile('pair', 'en');

        $this->assertSame('Markus', $data['author']);
        $this->assertStringContainsString('Hello.', $data['htmloutput']);
    }

    public function testSortedIdsOfTranslationUseDateOfDefaultLanguage(): void
    {
        $this->writeWisdom('older', "---\ntitle: A\ndate: 2026-01-01\n---\n\n# A");
        $this->writeWisdom('newer', "---\ntitle: B\ndate: 2026-02-01\n---\n\n# B");
        $this->writeWisdom('older', "---\ntitle: A en\n---\n\n# A en", 'en');
        $this->writeWisdom('newer', "---\ntitle: B en\n---\n\n# B en", 'en');

        $this->assertSame(['newer', 'older'], $this->guruWisdom->getSortedWisdomIds(null, 'en'));
    }
}
