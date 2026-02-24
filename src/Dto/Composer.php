<?php

namespace Tygygydyk\ComposerDependencyScanner\Dto;

final readonly class Composer
{
    /**
     * @param string           $php             PHP version from require.php
     * @param list<Dependency> $dependencies
     * @param list<Dependency> $dependenciesDev
     */
    public function __construct(
        public string $php,
        public array $dependencies,
        public array $dependenciesDev,
    ) {}
}
