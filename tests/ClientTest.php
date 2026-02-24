<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Tygygydyk\ComposerDependencyScanner\Client;
use Tygygydyk\ComposerDependencyScanner\Dto\Composer;
use Tygygydyk\ComposerDependencyScanner\Dto\Project;
use Tygygydyk\ComposerDependencyScanner\ProjectProvider\ProjectListProviderInterface;

/**
 * @internal
 *
 * @coversNothing
 */
final class ClientTest extends TestCase
{
    public function testGetComposerProjectsYieldsFromProvider(): void
    {
        $composer = new Composer('^8.2', [], []);
        $project = new Project('1', 'test/project', $composer);

        $provider = $this->createMock(ProjectListProviderInterface::class);
        $provider->method('getComposerProjects')
            ->willReturnCallback(static function () use ($project) {
                yield $project;
            })
        ;

        $client = new Client($provider);
        $projects = iterator_to_array($client->getComposerProjects());

        $this->assertCount(1, $projects);
        $this->assertSame($project, $projects[0]);
        $this->assertSame('1', $projects[0]->id);
        $this->assertSame('test/project', $projects[0]->name);
    }

    public function testGetComposerProjectsYieldsNothingWhenProviderEmpty(): void
    {
        $provider = $this->createMock(ProjectListProviderInterface::class);
        $provider->method('getComposerProjects')
            ->willReturnCallback(static function () {
                return;

                yield;
            })
        ;

        $client = new Client($provider);
        $projects = iterator_to_array($client->getComposerProjects());

        $this->assertCount(0, $projects);
    }
}
