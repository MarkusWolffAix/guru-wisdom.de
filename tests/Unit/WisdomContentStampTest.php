<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Service\GuruWisdomService;
use PHPUnit\Framework\TestCase;
use Yiisoft\Aliases\Aliases;

/**
 * The content stamp is part of the cache key of the wisdom index. It has to change whenever
 * the index could change, and stay the same otherwise.
 */
final class WisdomContentStampTest extends TestCase
{
    private string $root;
    private GuruWisdomService $service;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/wisdom_stamp_' . uniqid('', true);
        foreach (['public/wisdoms/en', 'drafts'] as $dir) {
            mkdir($this->root . '/' . $dir, 0777, true);
        }
        $this->write('public/wisdoms/A.md', "---\ntitle: \"A\"\n---\n\n# A\n");
        $this->write('public/wisdoms/B.md', "---\ntitle: \"B\"\n---\n\n# B\n");
        $this->write('public/wisdoms/en/A.md', "---\ntitle: \"A en\"\n---\n\n# A en\n");

        $aliases = new Aliases([
            '@public/wisdoms' => $this->root . '/public/wisdoms',
            '@drafts' => $this->root . '/drafts',
        ]);
        $this->service = new GuruWisdomService($aliases, false);
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

    private function write(string $relativePath, string $content): void
    {
        file_put_contents($this->root . '/' . $relativePath, $content);
    }

    public function testStampIsStableWithoutChanges(): void
    {
        $this->assertSame($this->service->getContentStamp('de'), $this->service->getContentStamp('de'));
    }

    public function testStampChangesWhenAFileIsAdded(): void
    {
        $before = $this->service->getContentStamp('de');
        $this->write('public/wisdoms/C.md', "---\ntitle: \"C\"\n---\n\n# C\n");

        $this->assertNotSame($before, $this->service->getContentStamp('de'));
    }

    public function testStampChangesWhenTheSizeChanges(): void
    {
        $before = $this->service->getContentStamp('de');
        // Written within the same second as the setUp() files, so the size has to catch the change.
        $this->write('public/wisdoms/B.md', "---\ntitle: \"B changed\"\n---\n\n# B\n");

        $this->assertNotSame($before, $this->service->getContentStamp('de'));
    }

    public function testStampChangesWhenOnlyTheModificationTimeChanges(): void
    {
        $before = $this->service->getContentStamp('de');
        touch($this->root . '/public/wisdoms/B.md', time() + 100);

        $this->assertNotSame($before, $this->service->getContentStamp('de'));
    }

    public function testEnglishStampFollowsTheGermanBaseFile(): void
    {
        $before = $this->service->getContentStamp('en');
        // The English page inherits front matter from the German file, so editing that one counts.
        touch($this->root . '/public/wisdoms/A.md', time() + 100);

        $this->assertNotSame($before, $this->service->getContentStamp('en'));
    }

    public function testStampDiffersWhenDraftsAreIncluded(): void
    {
        $aliases = new Aliases([
            '@public/wisdoms' => $this->root . '/public/wisdoms',
            '@drafts' => $this->root . '/drafts',
        ]);
        $withDrafts = new GuruWisdomService($aliases, true);

        $this->assertNotSame($this->service->getContentStamp('de'), $withDrafts->getContentStamp('de'));
    }
}
