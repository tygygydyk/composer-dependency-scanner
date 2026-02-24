<?php

namespace Tygygydyk\ComposerDependencyScanner\Dto;

final readonly class Project
{
    public function __construct(
        public string $id,
        public string $name,
        public Composer $composer,
    ) {}
}
