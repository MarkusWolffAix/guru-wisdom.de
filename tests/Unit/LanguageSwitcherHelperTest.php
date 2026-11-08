<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Web\Shared\LanguageSwitcherHelper;
use PHPUnit\Framework\TestCase;

/**
 * The DE | EN switch keeps the path on the partner domain and never leads to a 404.
 */
final class LanguageSwitcherHelperTest extends TestCase
{
    private const MULTILINGUAL = [
        'enable_lang_support_button' => true,
        'domain_switch_map' => [
            'guru-wisdom.de' => 'guru-wisdom.com',
            'guru-wisdom.com' => 'guru-wisdom.de',
            'localhost' => 'en.localhost',
            'en.localhost' => 'localhost',
        ],
    ];

    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/lang-switch-' . bin2hex(random_bytes(4));
        mkdir($this->dir . '/en', 0777, true);
        foreach (['Both.md', 'OnlyDe.md'] as $file) {
            file_put_contents($this->dir . '/' . $file, "---\n---\n");
        }
        foreach (['Both.md', 'OnlyEn.md'] as $file) {
            file_put_contents($this->dir . '/en/' . $file, "---\n---\n");
        }
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/{,en/}*.md', GLOB_BRACE) ?: [] as $file) {
            unlink($file);
        }
        rmdir($this->dir . '/en');
        rmdir($this->dir);
    }

    private function build(string $host, string $path, string $locale, string $query = '', array $ml = self::MULTILINGUAL): ?array
    {
        return LanguageSwitcherHelper::build($ml, $host, $path, $query, 'http', $this->dir, $locale);
    }

    public function testDisabledSwitchReturnsNull(): void
    {
        $this->assertSame(null, $this->build('guru-wisdom.de', '/', 'de', '', ['enable_lang_support_button' => false] + self::MULTILINGUAL));
    }

    public function testUnknownHostReturnsNull(): void
    {
        $this->assertSame(null, $this->build('example.org', '/', 'de'));
    }

    public function testWisdomKeepsPathAndUsesHttpsBehindProxy(): void
    {
        $result = $this->build('guru-wisdom.de', '/Both', 'de');
        $this->assertSame('https://guru-wisdom.com/Both', $result['url']);
        $this->assertSame(['DE', 'EN', 'English'], [$result['currentLabel'], $result['targetLabel'], $result['targetName']]);
        $this->assertSame(
            ['self' => 'https://guru-wisdom.de/Both', 'other' => 'https://guru-wisdom.com/Both'],
            $result['alternate']
        );
    }

    public function testEnglishToGerman(): void
    {
        $result = $this->build('guru-wisdom.com', '/Both', 'en');
        $this->assertSame('https://guru-wisdom.de/Both', $result['url']);
        $this->assertSame('Deutsch', $result['targetName']);
    }

    public function testFixedPagesExistInBothLanguages(): void
    {
        foreach (['/', '/contact', '/imprint', '/privacy'] as $path) {
            $result = $this->build('guru-wisdom.de', $path, 'de');
            $this->assertSame('https://guru-wisdom.com' . $path, $result['url'], $path);
            $this->assertNotSame(null, $result['alternate'], $path);
        }
    }

    public function testWisdomMissingInTargetLanguageLeadsToStartPage(): void
    {
        $result = $this->build('guru-wisdom.de', '/OnlyDe', 'de');
        $this->assertSame('https://guru-wisdom.com/', $result['url']);
        $this->assertSame(null, $result['alternate'], 'No hreflang for a page without counterpart.');

        $result = $this->build('guru-wisdom.com', '/OnlyEn', 'en');
        $this->assertSame('https://guru-wisdom.de/', $result['url']);
    }

    public function testUnknownPathLeadsToStartPage(): void
    {
        $this->assertSame('https://guru-wisdom.com/', $this->build('guru-wisdom.de', '/gibts-nicht', 'de')['url']);
        $this->assertSame('https://guru-wisdom.com/', $this->build('guru-wisdom.de', '/a/b', 'de')['url']);
    }

    public function testQueryIsKeptOnlyForPagesInBothLanguages(): void
    {
        $this->assertSame('https://guru-wisdom.com/Both?x=1', $this->build('guru-wisdom.de', '/Both', 'de', 'x=1')['url']);
        $this->assertSame('https://guru-wisdom.com/', $this->build('guru-wisdom.de', '/OnlyDe', 'de', 'x=1')['url']);
    }

    public function testPortIsIgnoredAndLocalhostKeepsScheme(): void
    {
        $this->assertSame('http://en.localhost/Both', $this->build('localhost:8080', '/Both', 'de')['url']);
    }
}
