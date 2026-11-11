<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Service\WisdomCacheService;
use PHPUnit\Framework\TestCase;

/**
 * In the overview the drafts (dev only) come first, then the published wisdoms.
 */
final class WisdomOverviewOrderTest extends TestCase
{
    public function testDraftsComeFirstAndKeepTheirOrder(): void
    {
        $input = [
            ['slug' => 'New', 'draft' => false],
            ['slug' => 'DraftA', 'draft' => true],
            ['slug' => 'Old', 'draft' => false],
            ['slug' => 'DraftB', 'draft' => true],
        ];

        $slugs = array_column(WisdomCacheService::draftsFirst($input), 'slug');

        $this->assertSame(['DraftA', 'DraftB', 'New', 'Old'], $slugs);
    }

    public function testWithoutDraftsNothingChanges(): void
    {
        $input = [
            ['slug' => 'New', 'draft' => false],
            ['slug' => 'Old'],
        ];

        $this->assertSame($input, WisdomCacheService::draftsFirst($input));
    }

    public function testEmptyListStaysEmpty(): void
    {
        $this->assertSame([], WisdomCacheService::draftsFirst([]));
    }
}
