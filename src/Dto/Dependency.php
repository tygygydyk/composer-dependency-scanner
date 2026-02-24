<?php

namespace Tygygydyk\ComposerDependencyScanner\Dto;

readonly class Dependency
{
    public function __construct(
        public string $name,
        public string $version,
        public ?string $versionLock = null,
    ) {}

    public function getNamespace(): string
    {
        $pos = strpos($this->name, '/');

        return substr($this->name, 0, false !== $pos ? $pos : 0);
    }

    public function isLocked(): bool
    {
        return null !== $this->versionLock;
    }
}
