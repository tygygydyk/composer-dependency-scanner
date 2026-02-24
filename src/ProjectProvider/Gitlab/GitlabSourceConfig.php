<?php

namespace Tygygydyk\ComposerDependencyScanner\ProjectProvider\Gitlab;

final readonly class GitlabSourceConfig
{
    public function __construct(
        public int $perPage = 100,
        public bool $membership = true,
        /** Null = use default_branch from each project API response */
        public ?string $defaultBranch = null,
    ) {}
}
