<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Service\GuruWisdomService;
use App\Service\WisdomCacheService;
use PHPUnit\Framework\TestCase;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Cache\CacheInterface;

/**
 * A wisdom with a date in the future stays hidden until that date (Berlin time).
 * The overview, the detail page, the navigation and the sitemap all read the list of
 * WisdomCacheService::getSortedWisdoms(), so hiding it there hides it everywhere.
 */
final class WisdomScheduleTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/wisdom_schedule_' . uniqid('', true);
        foreach (['public/wisdoms/en', 'drafts'] as $dir) {
            mkdir($this->root . '/' . $dir, 0777, true);
        }
        file_put_contents($this->root . '/public/wisdoms/Live.md', "---\ntitle: \"Live\"\ndate: 2026-01-01\n---\n\n# Live\n");
        file_put_contents($this->root . '/public/wisdoms/Later.md', "---\ntitle: \"Later\"\ndate: 2099-01-01\n---\n\n# Later\n");
        // The English file has no date of its own: it inherits the one of the German file
        file_put_contents($this->root . '/public/wisdoms/en/Later.md', "---\ntitle: \"Later en\"\n---\n\n# Later en\n");
    }

    protected function tearDown(): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->root, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->root);
    }

    private function cacheService(bool $drafts): WisdomCacheService
    {
        $aliases = new Aliases([
            '@public/wisdoms' => $this->root . '/public/wisdoms',
            '@drafts' => $this->root . '/drafts',
        ]);
        // A cache that always calls the callback, so the test does not depend on cached data
        $cache = $this->createMock(CacheInterface::class);
        $cache->method('getOrSet')->willReturnCallback(static fn(mixed $key, callable $callable): mixed => $callable());

        return new WisdomCacheService($cache, new GuruWisdomService($aliases, $drafts));
    }

    private static function berlin(string $time): \DateTimeImmutable
    {
        return new \DateTimeImmutable($time, new \DateTimeZone(WisdomCacheService::TIMEZONE));
    }

    public function testFutureDateIsScheduled(): void
    {
        $this->assertTrue(WisdomCacheService::isScheduled('2026-11-18', self::berlin('2026-11-17 23:59:59')));
    }

    public function testDateIsVisibleFromMidnight(): void
    {
        $this->assertFalse(WisdomCacheService::isScheduled('2026-11-18', self::berlin('2026-11-18 00:00:00')));
        $this->assertFalse(WisdomCacheService::isScheduled('2026-11-18', self::berlin('2026-11-18 15:30:00')));
    }

    public function testPastDateIsNotScheduled(): void
    {
        $this->assertFalse(WisdomCacheService::isScheduled('2026-11-17', self::berlin('2026-11-18 10:00:00')));
    }

    public function testDateWithTimeIsVisibleFromThatTime(): void
    {
        $this->assertTrue(WisdomCacheService::isScheduled('2026-11-18 08:00', self::berlin('2026-11-18 07:59:00')));
        $this->assertFalse(WisdomCacheService::isScheduled('2026-11-18 08:00', self::berlin('2026-11-18 08:00:00')));
    }

    public function testDateWithoutLeadingZerosIsRead(): void
    {
        $this->assertTrue(WisdomCacheService::isScheduled('2026-11-7', self::berlin('2026-11-06 12:00:00')));
        $this->assertFalse(WisdomCacheService::isScheduled('2026-11-7', self::berlin('2026-11-07 12:00:00')));
    }

    public function testMissingOrUnreadableDateIsNeverScheduled(): void
    {
        $now = self::berlin('2026-11-18 10:00:00');

        $this->assertFalse(WisdomCacheService::isScheduled(null, $now));
        $this->assertFalse(WisdomCacheService::isScheduled('', $now));
        $this->assertFalse(WisdomCacheService::isScheduled('not a date', $now));
    }

    public function testScheduledWisdomsAreRemovedAndOrderIsKept(): void
    {
        $now = self::berlin('2026-11-18 10:00:00');
        $input = [
            ['slug' => 'Future', 'date' => '2026-11-19'],
            ['slug' => 'Today', 'date' => '2026-11-18'],
            ['slug' => 'Old', 'date' => '2026-01-01'],
        ];

        $slugs = array_column(WisdomCacheService::applySchedule($input, $now, false), 'slug');

        $this->assertSame(['Today', 'Old'], $slugs);
    }

    public function testDevKeepsScheduledWisdomsAndMarksThem(): void
    {
        $now = self::berlin('2026-11-18 10:00:00');
        $input = [
            ['slug' => 'Future', 'date' => '2026-11-19'],
            ['slug' => 'Today', 'date' => '2026-11-18'],
        ];

        $result = WisdomCacheService::applySchedule($input, $now, true);

        $this->assertSame(['Future', 'Today'], array_column($result, 'slug'));
        $this->assertTrue($result[0]['scheduled']);
        $this->assertArrayNotHasKey('scheduled', $result[1]);
    }

    public function testIndexHidesTheScheduledWisdomWithoutDrafts(): void
    {
        $service = $this->cacheService(false);

        $this->assertSame(['Live'], array_column($service->getSortedWisdoms('de'), 'slug'));
        $this->assertNull($service->getLatestWisdom('en'));
    }

    public function testScheduledWisdomIsNotAnAnswerOfTheNeighbourLogic(): void
    {
        $neighbors = $this->cacheService(false)->getNeighbors('Live', 'de');

        $this->assertNull($neighbors['newer']);
        $this->assertNull($neighbors['older']);
    }

    public function testIndexInDevShowsTheScheduledWisdomMarked(): void
    {
        $wisdoms = $this->cacheService(true)->getSortedWisdoms('de');

        $this->assertSame(['Later', 'Live'], array_column($wisdoms, 'slug'));
        $this->assertTrue($wisdoms[0]['scheduled']);
        $this->assertArrayNotHasKey('scheduled', $wisdoms[1]);
    }
}
