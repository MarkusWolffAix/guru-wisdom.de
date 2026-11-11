<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Every wisdom exists exactly once per language: public/wisdoms/<Id>.md (German) and
 * public/wisdoms/en/<Id>.md (English) belong together. The file name is the ID.
 */
final class WisdomPairsTest extends TestCase
{
    private const DE_DIR = __DIR__ . '/../../public/wisdoms';
    private const EN_DIR = __DIR__ . '/../../public/wisdoms/en';

    /**
     * Known gaps: IDs that exist in one language only because the translation is still to do.
     * When you add the file, remove the ID here. The test fails if an entry is no longer a gap.
     * New gaps that are not listed here make the test fail.
     */
    private const MISSING_IN_DE = [];
    private const MISSING_IN_EN = [];

    /**
     * @return list<string> Sorted IDs (file names without ".md") of a language folder.
     */
    private function ids(string $dir): array
    {
        $ids = [];
        foreach (glob($dir . '/*.md') ?: [] as $file) {
            $ids[] = basename($file, '.md');
        }
        sort($ids);

        return $ids;
    }

    private function format(array $ids): string
    {
        return $ids === [] ? '(none)' : implode(', ', $ids);
    }

    public function testEveryWisdomExistsInBothLanguages(): void
    {
        $de = $this->ids(self::DE_DIR);
        $en = $this->ids(self::EN_DIR);

        $missingInDe = array_values(array_diff($en, $de));
        $missingInEn = array_values(array_diff($de, $en));
        $expectedInDe = self::MISSING_IN_DE;
        $expectedInEn = self::MISSING_IN_EN;
        sort($expectedInDe);
        sort($expectedInEn);

        $problems = [];
        if ($missingInDe !== $expectedInDe) {
            $problems[] = 'German files missing: ' . $this->format($missingInDe)
                . ' (expected gaps in MISSING_IN_DE: ' . $this->format($expectedInDe) . ')';
        }
        if ($missingInEn !== $expectedInEn) {
            $problems[] = 'English files missing: ' . $this->format($missingInEn)
                . ' (expected gaps in MISSING_IN_EN: ' . $this->format($expectedInEn) . ')';
        }

        $this->assertSame(
            [],
            $problems,
            implode('. ', $problems) . '. Add the missing file, or update the list of known gaps.'
        );
    }

    public function testNamesDoNotDifferOnlyByCase(): void
    {
        // macOS usually ignores case in file names, the Linux server does not.
        $byLowercase = [];
        foreach (array_merge($this->ids(self::DE_DIR), $this->ids(self::EN_DIR)) as $id) {
            $byLowercase[strtolower($id)][$id] = true;
        }

        $conflicts = [];
        foreach ($byLowercase as $spellings) {
            if (count($spellings) > 1) {
                $conflicts[] = implode(' / ', array_keys($spellings));
            }
        }

        $this->assertSame([], $conflicts, 'File names that differ only by case: ' . $this->format($conflicts));
    }

    public function testNamesContainOnlyAllowedCharacters(): void
    {
        $invalid = [];
        foreach (array_merge($this->ids(self::DE_DIR), $this->ids(self::EN_DIR)) as $id) {
            if (preg_match('/^[A-Za-z0-9_-]+$/', $id) !== 1) {
                $invalid[] = $id;
            }
        }

        $this->assertSame([], $invalid, 'File names with characters that are not allowed in an ID: ' . $this->format($invalid));
    }
}
