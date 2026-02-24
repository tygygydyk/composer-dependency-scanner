<?php

namespace App\Tests;

final class FixtureHelper
{
    private static ?string $basePath = null;

    public static function path(string ...$parts): string
    {
        if (null === self::$basePath) {
            self::$basePath = dirname(__DIR__).'/tests/fixtures';
        }

        return self::$basePath.'/'.implode('/', $parts);
    }

    /** @return array<mixed> */
    public static function loadJson(string ...$parts): array
    {
        $path = self::path(...$parts);
        $content = file_get_contents($path);
        if (false === $content) {
            throw new \RuntimeException("Fixture not found: {$path}");
        }

        $data = json_decode($content, true);
        if (!is_array($data)) {
            throw new \RuntimeException("Invalid JSON fixture: {$path}");
        }

        return $data;
    }

    /** GitLab file API returns { "content": "<base64>" }. */
    public static function loadGitlabFileContent(string ...$parts): string
    {
        $path = self::path(...$parts);
        $content = file_get_contents($path);
        if (false === $content) {
            throw new \RuntimeException("Fixture not found: {$path}");
        }

        return base64_encode($content);
    }
}
