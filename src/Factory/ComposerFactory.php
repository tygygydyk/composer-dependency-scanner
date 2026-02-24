<?php

namespace Tygygydyk\ComposerDependencyScanner\Factory;

use Tygygydyk\ComposerDependencyScanner\Dto\Composer;
use Tygygydyk\ComposerDependencyScanner\Dto\Dependency;

final class ComposerFactory
{
    /**
     * TODO add composer file validator.
     *
     * @param array<mixed>      $composerJson composer.json body
     * @param null|array<mixed> $composerLock composer.lock body
     */
    public function create(array $composerJson, ?array $composerLock): Composer
    {
        $composerLockFormatted = [];
        if (null !== $composerLock) {
            $packages = isset($composerLock['packages']) && is_array($composerLock['packages']) ? $composerLock['packages'] : [];
            $packagesDev = isset($composerLock['packages-dev']) && is_array($composerLock['packages-dev']) ? $composerLock['packages-dev'] : [];
            foreach (array_merge($packages, $packagesDev) as $package) {
                if (is_array($package) && isset($package['name'], $package['version'])
                    && is_string($package['name']) && is_string($package['version'])) {
                    $composerLockFormatted[$package['name']] = $package['version'];
                }
            }
        }

        $lockRequire = null !== $composerLock && isset($composerLock['require']) && is_array($composerLock['require'])
            ? ($composerLock['require']['php'] ?? null)
            : null;
        $jsonRequire = isset($composerJson['require']) && is_array($composerJson['require'])
            ? ($composerJson['require']['php'] ?? null)
            : null;
        $php = (is_string($lockRequire) ? $lockRequire : null) ?? (is_string($jsonRequire) ? $jsonRequire : null) ?? 'UNKNOWN';

        /** @var array<string, string> $require */
        $require = isset($composerJson['require']) && is_array($composerJson['require']) ? $composerJson['require'] : [];

        /** @var array<string, string> $requireDev */
        $requireDev = isset($composerJson['require-dev']) && is_array($composerJson['require-dev']) ? $composerJson['require-dev'] : [];

        return new Composer(
            php: $php,
            dependencies: $this->extractDependencies($require, $composerLockFormatted),
            dependenciesDev: $this->extractDependencies($requireDev, $composerLockFormatted),
        );
    }

    /**
     * @param array<string, string> $require
     * @param array<string, string> $composerLockFormatted
     *
     * @return list<Dependency>
     */
    private function extractDependencies(array $require, array $composerLockFormatted): array
    {
        $packages = [];
        foreach ($require as $name => $version) {
            if (str_starts_with($name, 'ext') || 'php' === $name) {
                continue;
            }

            $packages[] = new Dependency(
                name: $name,
                version: $version,
                versionLock: $composerLockFormatted[$name] ?? null,
            );
        }

        return $packages;
    }
}
