<?php

namespace App\Tests\Model;

use PHPUnit\Framework\TestCase;
use Tygygydyk\ComposerDependencyScanner\Dto\Composer;
use Tygygydyk\ComposerDependencyScanner\Dto\Project;

/**
 * @internal
 *
 * @coversNothing
 */
final class ProjectTest extends TestCase
{
    public function testConstructor(): void
    {
        $composer = new Composer('^8.2', [], []);
        $project = new Project('123', 'group/name', $composer);

        $this->assertSame('123', $project->id);
        $this->assertSame('group/name', $project->name);
        $this->assertSame($composer, $project->composer);
    }
}
