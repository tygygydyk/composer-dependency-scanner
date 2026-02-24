<?php

namespace Tygygydyk\ComposerDependencyScanner;

use Tygygydyk\ComposerDependencyScanner\Dto\Project;
use Tygygydyk\ComposerDependencyScanner\ProjectProvider\ProjectListProviderInterface;

final readonly class Client implements ClientInterface
{
    public function __construct(
        private ProjectListProviderInterface $projectListProvider,
    ) {}

    /**
     * @return \Generator<Project>
     */
    public function getComposerProjects(): \Generator
    {
        yield from $this->projectListProvider->getComposerProjects();
    }
}
