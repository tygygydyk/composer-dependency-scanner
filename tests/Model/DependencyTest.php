<?php

namespace App\Tests\Model;

use PHPUnit\Framework\TestCase;
use Tygygydyk\ComposerDependencyScanner\Dto\Dependency;

/**
 * @internal
 *
 * @coversNothing
 */
final class DependencyTest extends TestCase
{
    public function testConstructorStoresNameVersionAndVersionLock(): void
    {
        $d = new Dependency('symfony/console', '^6.0', '6.0.1');

        $this->assertSame('symfony/console', $d->name);
        $this->assertSame('^6.0', $d->version);
        $this->assertSame('6.0.1', $d->versionLock);
    }

    public function testVersionLockIsOptional(): void
    {
        $d = new Dependency('vendor/package', '^1.0');

        $this->assertNull($d->versionLock);
    }

    public function testGetNamespaceReturnsFirstSegment(): void
    {
        $d = new Dependency('symfony/console', '^6.0');

        $this->assertSame('symfony', $d->getNamespace());
    }

    public function testGetNamespaceReturnsEmptyStringWhenNoSlash(): void
    {
        $d = new Dependency('php', '^8.2');

        $this->assertSame('', $d->getNamespace());
    }

    public function testIsLockedReturnsTrueWhenVersionLockSet(): void
    {
        $d = new Dependency('vendor/package', '^1.0', '1.2.3');

        $this->assertTrue($d->isLocked());
    }

    public function testIsLockedReturnsFalseWhenVersionLockNull(): void
    {
        $d = new Dependency('vendor/package', '^1.0');

        $this->assertFalse($d->isLocked());
    }
}
