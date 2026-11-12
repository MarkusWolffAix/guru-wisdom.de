<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Service\SitemapBuilder;
use PHPUnit\Framework\TestCase;

/**
 * sitemap.xml and robots.txt are built per domain from the published wisdoms.
 */
final class SitemapBuilderTest extends TestCase
{
    private const SWITCH_MAP = [
        'guru-wisdom.de' => 'guru-wisdom.com',
        'www.guru-wisdom.de' => 'guru-wisdom.com',
        'guru-wisdom.com' => 'guru-wisdom.de',
        'www.guru-wisdom.com' => 'guru-wisdom.de',
        'test.guru-wisdom.de' => 'test.guru-wisdom.com',
    ];

    public function testBaseUrlIsAlwaysHttpsWithoutWwwForPublicHosts(): void
    {
        $this->assertSame('https://guru-wisdom.de', SitemapBuilder::baseUrl('www.guru-wisdom.de', 'http', 80));
        $this->assertSame('https://guru-wisdom.com', SitemapBuilder::baseUrl('Guru-Wisdom.com:443'));
    }

    public function testLocalHostKeepsSchemeAndPort(): void
    {
        $this->assertSame('http://en.localhost:8080', SitemapBuilder::baseUrl('en.localhost', 'http', 8080));
        $this->assertSame('http://localhost', SitemapBuilder::baseUrl('localhost', 'http', 80));
    }

    public function testPartnerBaseUrl(): void
    {
        $this->assertSame('https://guru-wisdom.com', SitemapBuilder::partnerBaseUrl(self::SWITCH_MAP, 'guru-wisdom.de'));
        $this->assertSame('https://guru-wisdom.de', SitemapBuilder::partnerBaseUrl(self::SWITCH_MAP, 'guru-wisdom.com'));
        $this->assertNull(SitemapBuilder::partnerBaseUrl(self::SWITCH_MAP, 'unknown.example'));
    }

    public function testDraftsAreNeverListed(): void
    {
        $xml = SitemapBuilder::sitemap(
            [
                ['slug' => 'Published', 'date' => '2026-11-10', 'draft' => false],
                ['slug' => 'Secret', 'date' => '2026-11-11', 'draft' => true],
            ],
            'https://guru-wisdom.de',
            'de',
        );

        $this->assertStringContainsString('<loc>https://guru-wisdom.de/Published</loc>', $xml);
        $this->assertStringNotContainsString('Secret', $xml);
    }

    public function testStaticPagesAreListedAndOldGermanPathsAreNot(): void
    {
        $xml = SitemapBuilder::sitemap([], 'https://guru-wisdom.de', 'de');

        foreach (['/', '/contact', '/imprint', '/privacy'] as $path) {
            $this->assertStringContainsString('<loc>https://guru-wisdom.de' . $path . '</loc>', $xml);
        }
        foreach (['/kontakt', '/impressum', '/datenschutz'] as $legacy) {
            $this->assertStringNotContainsString($legacy, $xml);
        }
    }

    public function testLastmodIsNormalisedAndStartPageUsesTheNewest(): void
    {
        $xml = SitemapBuilder::sitemap(
            [
                ['slug' => 'Old', 'date' => '2026-4-7'],
                ['slug' => 'New', 'date' => '2026-11-10'],
            ],
            'https://guru-wisdom.de',
            'de',
        );

        $this->assertStringContainsString('<lastmod>2026-04-07</lastmod>', $xml);
        $this->assertSame(1, preg_match('#<loc>https://guru-wisdom.de/</loc>\s*<lastmod>2026-11-10</lastmod>#', $xml));
    }

    public function testHreflangOnlyWhenThePartnerPageExists(): void
    {
        $xml = SitemapBuilder::sitemap(
            [['slug' => 'Both', 'date' => '2026-11-10'], ['slug' => 'OnlyDe', 'date' => '2026-11-09']],
            'https://guru-wisdom.de',
            'de',
            'https://guru-wisdom.com',
            [['slug' => 'Both', 'date' => '2026-11-10']],
        );

        $this->assertStringContainsString('hreflang="de" href="https://guru-wisdom.de/Both"', $xml);
        $this->assertStringContainsString('hreflang="en" href="https://guru-wisdom.com/Both"', $xml);
        $this->assertStringContainsString('hreflang="x-default" href="https://guru-wisdom.de/Both"', $xml);
        $this->assertStringNotContainsString('guru-wisdom.com/OnlyDe', $xml);
    }

    public function testXDefaultIsAlwaysTheGermanPage(): void
    {
        $xml = SitemapBuilder::sitemap(
            [['slug' => 'Both', 'date' => '2026-11-10']],
            'https://guru-wisdom.com',
            'en',
            'https://guru-wisdom.de',
            [['slug' => 'Both', 'date' => '2026-11-10']],
        );

        $this->assertStringContainsString('hreflang="x-default" href="https://guru-wisdom.de/Both"', $xml);
        $this->assertStringContainsString('hreflang="en" href="https://guru-wisdom.com/Both"', $xml);
    }

    public function testXmlIsWellFormed(): void
    {
        $xml = SitemapBuilder::sitemap(
            [['slug' => 'A-b_1', 'date' => '2026-11-10']],
            'https://guru-wisdom.de',
            'de',
            'https://guru-wisdom.com',
            [['slug' => 'A-b_1']],
        );

        $document = new \DOMDocument();
        $this->assertTrue($document->loadXML($xml));
        $this->assertSame(5, $document->getElementsByTagName('url')->length);
    }

    public function testRobotsPointsToTheSitemapOfTheDomain(): void
    {
        $this->assertSame(
            "User-agent: *\nDisallow:\n\nSitemap: https://guru-wisdom.com/sitemap.xml\n",
            SitemapBuilder::robots('https://guru-wisdom.com', true),
        );
    }

    public function testTestAndLocalHostsAreClosedForCrawlers(): void
    {
        $this->assertTrue(SitemapBuilder::crawlingAllowed('guru-wisdom.de'));
        $this->assertTrue(SitemapBuilder::crawlingAllowed('www.guru-wisdom.com'));
        $this->assertFalse(SitemapBuilder::crawlingAllowed('test.guru-wisdom.de'));
        $this->assertFalse(SitemapBuilder::crawlingAllowed('localhost'));
        $this->assertSame("User-agent: *\nDisallow: /\n", SitemapBuilder::robots('https://test.guru-wisdom.de', false));
    }
}
