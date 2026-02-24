<?php

namespace App\Tests\Source;

use App\Tests\FixtureHelper;
use PHPUnit\Framework\TestCase;
use Tygygydyk\ComposerDependencyScanner\Dto\Project;
use Tygygydyk\ComposerDependencyScanner\Factory\ComposerFactory;
use Tygygydyk\ComposerDependencyScanner\ProjectProvider\Gitlab\GitlabSource;
use Tygygydyk\ComposerDependencyScanner\ProjectProvider\Gitlab\GitlabSourceConfig;
use Tygygydyk\ComposerDependencyScanner\Transport\Http\HttpClientInterface;

/**
 * @internal
 *
 * @coversNothing
 */
final class GitlabSourceTest extends TestCase
{
    public function testGetComposerProjectsYieldsOnlyProjectsWithComposerJson(): void
    {
        $http = $this->createMock(HttpClientInterface::class);
        $http->method('getStatusCode')
            ->with(self::stringContains('composer.json'))
            ->willReturn(200)
        ;
        $http->method('get')
            ->willReturnCallback(function (string $url) {
                if (str_contains($url, 'page=2')) {
                    return FixtureHelper::loadJson('gitlab', 'projects-empty.json');
                }
                if (str_contains($url, 'page=1') && !str_contains($url, 'repository')) {
                    return FixtureHelper::loadJson('gitlab', 'projects-page1.json');
                }
                if (str_contains($url, 'composer.json?')) {
                    return [
                        'content' => FixtureHelper::loadGitlabFileContent('composer', 'minimal.json'),
                    ];
                }
                if (str_contains($url, 'composer.lock?')) {
                    return [
                        'content' => FixtureHelper::loadGitlabFileContent('composer', 'minimal.lock'),
                    ];
                }

                return [];
            })
        ;

        $source = new GitlabSource($http, new ComposerFactory());
        $projects = iterator_to_array($source->getComposerProjects());

        $this->assertCount(1, $projects);
        $this->assertInstanceOf(Project::class, $projects[0]);
        $this->assertSame('42', $projects[0]->id);
        $this->assertSame('group/proj', $projects[0]->name);
        $this->assertSame('^8.2', $projects[0]->composer->php);
        $this->assertCount(1, $projects[0]->composer->dependencies);
        $this->assertSame('symfony/console', $projects[0]->composer->dependencies[0]->name);
    }

    public function testGetComposerProjectsSkipsProjectWithoutComposerJson(): void
    {
        $http = $this->createMock(HttpClientInterface::class);
        $http->method('getStatusCode')
            ->with(self::stringContains('composer.json'))
            ->willReturn(404)
        ;
        $http->method('get')
            ->willReturnCallback(function (string $url) {
                if (str_contains($url, 'page=2')) {
                    return FixtureHelper::loadJson('gitlab', 'projects-empty.json');
                }
                if (str_contains($url, 'page=1') && !str_contains($url, 'repository')) {
                    return FixtureHelper::loadJson('gitlab', 'projects-one-no-composer.json');
                }

                return [];
            })
        ;

        $source = new GitlabSource($http, new ComposerFactory());
        $projects = iterator_to_array($source->getComposerProjects());

        $this->assertCount(0, $projects);
    }

    public function testGetComposerProjectsStopsWhenEmptyPage(): void
    {
        $http = $this->createMock(HttpClientInterface::class);
        $http->method('getStatusCode')->willReturn(200);
        $http->method('get')
            ->willReturnCallback(function (string $url) {
                if (str_contains($url, 'page=2')) {
                    return FixtureHelper::loadJson('gitlab', 'projects-empty.json');
                }
                if (str_contains($url, 'page=1') && !str_contains($url, 'repository')) {
                    return FixtureHelper::loadJson('gitlab', 'projects-page1-one-project.json');
                }
                if (str_contains($url, 'composer.json?')) {
                    return [
                        'content' => FixtureHelper::loadGitlabFileContent('composer', 'minimal-only-php.json'),
                    ];
                }
                if (str_contains($url, 'composer.lock?')) {
                    return [
                        'content' => FixtureHelper::loadGitlabFileContent('composer', 'only-php-ext.lock'),
                    ];
                }

                return [];
            })
        ;

        $source = new GitlabSource($http, new ComposerFactory());
        $projects = iterator_to_array($source->getComposerProjects());

        $this->assertCount(1, $projects);
    }

    public function testGetComposerProjectsUsesConfigDefaultBranch(): void
    {
        $http = $this->createMock(HttpClientInterface::class);
        $http->method('getStatusCode')->willReturn(200);
        $http->method('get')
            ->willReturnCallback(function (string $url) {
                if (str_contains($url, 'page=2')) {
                    return FixtureHelper::loadJson('gitlab', 'projects-empty.json');
                }
                if (str_contains($url, 'page=1') && !str_contains($url, 'repository')) {
                    return FixtureHelper::loadJson('gitlab', 'projects-page1-one-project.json');
                }
                if (str_contains($url, 'ref=stable')) {
                    return [
                        'content' => FixtureHelper::loadGitlabFileContent('composer', 'minimal-only-php.json'),
                    ];
                }
                if (str_contains($url, 'composer.lock?ref=stable')) {
                    return [
                        'content' => FixtureHelper::loadGitlabFileContent('composer', 'only-php-ext.lock'),
                    ];
                }

                return [];
            })
        ;

        $config = new GitlabSourceConfig(100, true, 'stable');
        $source = new GitlabSource($http, new ComposerFactory(), $config);
        $projects = iterator_to_array($source->getComposerProjects());

        $this->assertCount(1, $projects);
        $this->assertSame('^8.2', $projects[0]->composer->php);
    }
}
