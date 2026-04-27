<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Environment;
use PHPUnit\Framework\TestCase;

final class EnvironmentTest extends TestCase
{
    protected function setUp(): void
    {
        // Wir setzen die Variable manuell, damit das Framework glücklich ist
        $_SERVER['APP_ENV'] = 'test';
        $_ENV['APP_ENV'] = 'test';
        putenv('APP_ENV=test');

        Environment::prepare();
    }

    public function testAppEnv(): void
    {
        $this->assertSame('test', Environment::appEnv());
    }
}
