<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Service\GuruWisdomService;
use PHPUnit\Framework\TestCase;
use Yiisoft\Aliases\Aliases;

/**
 * Drafts (folder /drafts, outside of the web root) are only visible in the dev environment.
 */
final class WisdomDraftsTest extends TestCase
{
    private string $root;
    private Aliases $aliases;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/wisdom_drafts_' . uniqid('', true);
        foreach (['public/wisdoms/en', 'drafts/en'] as $dir) {
            mkdir($this->root . '/' . $dir, 0777, true);
        }

        $this->write('public/wisdoms/Published.md', "---\ntitle: \"Published\"\ndate: 2026-01-01\n---\n\n# Published\n");
        $this->write('public/wisdoms/Both.md', "---\ntitle: \"Both public\"\ndate: 2026-01-02\n---\n\n# Both public\n");
        $this->write('drafts/Both.md', "---\ntitle: \"Both draft\"\ndate: 2026-03-01\n---\n\n# Both draft\n");
        $this->write('drafts/OnlyDraft.md', "---\ntitle: \"Only draft\"\ndate: 2026-02-01\ntags: [\"A\"]\n---\n\n# Only draft\n");
        $this->write('drafts/en/OnlyDraft.md', "---\ntitle: \"Only draft EN\"\n---\n\n# Only draft EN\n");

        $this->aliases = new Aliases([
            '@public/wisdoms' => $this->root . '/public/wisdoms',
            '@drafts' => $this->root . '/drafts',
        ]);
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

    public function testDraftsAreHiddenWhenDisabled(): void
    {
        $service = new GuruWisdomService($this->aliases, false);

        $ids = $service->getAllIds('de');
        sort($ids);
        $this->assertSame(['Both', 'Published'], $ids);
        $this->assertSame([], $service->getFrontMatter('OnlyDraft', 'de'));
        $this->assertSame([], $service->parseFile('OnlyDraft', 'de'));
        $this->assertNotContains('OnlyDraft', $service->getSortedWisdomIds(null, 'de'));
        $this->assertFalse($service->getFrontMatter('Published', 'de')['draft']);
    }

    public function testDraftsAreShownAndMarkedWhenEnabled(): void
    {
        $service = new GuruWisdomService($this->aliases, true);

        $ids = $service->getAllIds('de');
        sort($ids);
        $this->assertSame(['Both', 'OnlyDraft', 'Published'], $ids);

        $draft = $service->getFrontMatter('OnlyDraft', 'de');
        $this->assertTrue($draft['draft']);
        $this->assertSame('Only draft', $draft['title']);
        $this->assertFalse($service->getFrontMatter('Published', 'de')['draft']);
        $this->assertSame('Only draft', $service->parseFile('OnlyDraft', 'de')['title']);
    }

    public function testPublishedWisdomWinsOverDraftWithSameId(): void
    {
        $service = new GuruWisdomService($this->aliases, true);

        $data = $service->getFrontMatter('Both', 'de');
        $this->assertSame('Both public', $data['title']);
        $this->assertFalse($data['draft']);
        $this->assertSame(1, count(array_keys($service->getAllIds('de'), 'Both', true)));
    }

    public function testEnglishDraftInheritsFromGermanDraft(): void
    {
        $service = new GuruWisdomService($this->aliases, true);

        $data = $service->getFrontMatter('OnlyDraft', 'en');
        $this->assertTrue($data['draft']);
        $this->assertSame('Only draft EN', $data['title']);
        $this->assertSame(['"A"'], $data['tags']);
        $this->assertContains('OnlyDraft', $service->getAllIds('en'));
    }

    public function testMissingDraftsAliasDisablesDrafts(): void
    {
        $aliases = new Aliases(['@public/wisdoms' => $this->root . '/public/wisdoms']);
        $service = new GuruWisdomService($aliases, true);

        $this->assertFalse($service->areDraftsEnabled());
        $this->assertSame([], $service->getFrontMatter('OnlyDraft', 'de'));
    }

    public function testFilesWithInvalidIdsAreIgnored(): void
    {
        $this->write('drafts/Broken.NP.md', "---\ntitle: \"Backup\"\n---\n\n# Backup\n");
        $this->write('public/wisdoms/Other.copy.md', "---\ntitle: \"Copy\"\n---\n\n# Copy\n");
        $service = new GuruWisdomService($this->aliases, true);

        $ids = $service->getAllIds('de');
        sort($ids);
        $this->assertSame(['Both', 'OnlyDraft', 'Published'], $ids);
        $this->assertSame(['OnlyDraft', 'Both', 'Published'], $service->getSortedWisdomIds(null, 'de'));
    }

    public function testSortingIncludesDraftsByDate(): void
    {
        $service = new GuruWisdomService($this->aliases, true);

        // Newest first: Both (2026-01-02 from the published file), OnlyDraft (02-01), Published (01-01)
        $sorted = $service->getSortedWisdomIds(null, 'de');
        $this->assertSame(['OnlyDraft', 'Both', 'Published'], $sorted);
    }
}
