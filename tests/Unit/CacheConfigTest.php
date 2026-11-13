<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

/**
 * Test and prod bind a persistent cache. In dev the default per-request ArrayCache stays,
 * so a new or edited wisdom or draft shows up immediately.
 */
final class CacheConfigTest extends TestCase
{
    private function load(array $params): array
    {
        return (static function (array $params): array {
            return require __DIR__ . '/../../config/common/di/cache.php';
        })($params);
    }

    public function testNoCacheIsBoundByDefault(): void
    {
        $this->assertSame([], $this->load([]));
    }

    public function testNoCacheIsBoundWhenSwitchedOff(): void
    {
        $this->assertSame([], $this->load(['wisdom' => ['persistentCache' => false]]));
    }

    public function testFileCacheIsBoundWhenSwitchedOn(): void
    {
        $this->assertArrayHasKey(CacheInterface::class, $this->load(['wisdom' => ['persistentCache' => true]]));
    }

    public function testOnlyDevWorksWithoutPersistentCache(): void
    {
        foreach (['dev' => false, 'test' => true, 'prod' => true] as $env => $expected) {
            $params = require __DIR__ . '/../../config/environments/' . $env . '/params.php';
            $this->assertSame($expected, (bool)($params['wisdom']['persistentCache'] ?? false), $env);
        }
    }
}
