<?php

namespace Tygygydyk\ComposerDependencyScanner\ProjectProvider;

use Tygygydyk\ComposerDependencyScanner\Dto\Project;

interface ProjectListProviderInterface
{
    /**
     * @return \Generator<Project>
     */
    public function getComposerProjects(): \Generator;
}
