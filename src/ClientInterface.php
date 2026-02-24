<?php

namespace Tygygydyk\ComposerDependencyScanner;

use Tygygydyk\ComposerDependencyScanner\Dto\Project;

interface ClientInterface
{
    /**
     * @return \Generator<Project>
     */
    public function getComposerProjects(): \Generator;
}
