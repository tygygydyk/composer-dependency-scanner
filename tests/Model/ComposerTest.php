<?php

namespace App\Tests\Model;

use App\Tests\FixtureHelper;
use PHPUnit\Framework\TestCase;
use Tygygydyk\ComposerDependencyScanner\Dto\Composer;
use Tygygydyk\ComposerDependencyScanner\Dto\Dependency;
use Tygygydyk\ComposerDependencyScanner\Factory\ComposerFactory;

/**
 * @internal
 *
 * @coversNothing
 */
final class ComposerTest extends TestCase
{
    public function testCreateFromMinimalComposerJson(): void
    {
        $json = FixtureHelper::loadJson('composer', 'minimal.json');
        $composer = $this->createFactory()->create($json, null);

        $this->assertSame('^8.2', $composer->php);
        $this->assertCount(1, $composer->dependencies);
        $this->assertSame('symfony/console', $composer->dependencies[0]->name);
        $this->assertSame('^6.0', $composer->dependencies[0]->version);
        $this->assertNull($composer->dependencies[0]->versionLock);
        $this->assertSame([], $composer->dependenciesDev);
    }

    public function testCreateSkipsPhpAndExt(): void
    {
        $json = FixtureHelper::loadJson('composer', 'skip-php-ext.json');
        $composer = $this->createFactory()->create($json, null);

        $this->assertCount(1, $composer->dependencies);
        $this->assertSame('vendor/package', $composer->dependencies[0]->name);
    }

    public function testCreateWithLockFillsVersionLock(): void
    {
        $json = FixtureHelper::loadJson('composer', 'minimal.json');
        $lock = FixtureHelper::loadJson('composer', 'minimal.lock');

        $composer = $this->createFactory()->create($json, $lock);

        $this->assertSame('^8.2', $composer->php);
        $this->assertCount(1, $composer->dependencies);
        $this->assertSame('6.0.0', $composer->dependencies[0]->versionLock);
    }

    public function testCreateWithRequireDevAndLock(): void
    {
        $json = FixtureHelper::loadJson('composer', 'with-dev.json');
        $lock = FixtureHelper::loadJson('composer', 'with-dev.lock');

        $composer = $this->createFactory()->create($json, $lock);

        $this->assertCount(1, $composer->dependencies);
        $this->assertCount(1, $composer->dependenciesDev);
        $this->assertSame('phpunit/phpunit', $composer->dependenciesDev[0]->name);
        $this->assertSame('10.0.1', $composer->dependenciesDev[0]->versionLock);
    }

    public function testCreateReadsVersionLockFromPackagesDev(): void
    {
        $json = FixtureHelper::loadJson('composer', 'dev-in-lock-dev.json');
        $lock = FixtureHelper::loadJson('composer', 'dev-in-lock-dev.lock');

        $composer = $this->createFactory()->create($json, $lock);

        $this->assertCount(1, $composer->dependenciesDev);
        $this->assertSame('10.0.1', $composer->dependenciesDev[0]->versionLock);
    }

    public function testCreatePhpUnknownWhenRequireEmpty(): void
    {
        $json = FixtureHelper::loadJson('composer', 'empty-require.json');

        $composer = $this->createFactory()->create($json, null);

        $this->assertSame('UNKNOWN', $composer->php);
    }

    public function testConstructor(): void
    {
        $deps = [new Dependency('v/p', '^1.0', null)];
        $composer = new Composer('^8.2', $deps, []);

        $this->assertSame('^8.2', $composer->php);
        $this->assertSame($deps, $composer->dependencies);
        $this->assertSame([], $composer->dependenciesDev);
    }

    private function createFactory(): ComposerFactory
    {
        return new ComposerFactory();
    }
}
