<?php

namespace Tygygydyk\ComposerDependencyScanner\ProjectProvider\Gitlab;

use Tygygydyk\ComposerDependencyScanner\Dto\Composer;
use Tygygydyk\ComposerDependencyScanner\Dto\Project;
use Tygygydyk\ComposerDependencyScanner\Exception\HttpException;
use Tygygydyk\ComposerDependencyScanner\Exception\InvalidComposerFileException;
use Tygygydyk\ComposerDependencyScanner\Factory\ComposerFactory;
use Tygygydyk\ComposerDependencyScanner\ProjectProvider\ProjectListProviderInterface;
use Tygygydyk\ComposerDependencyScanner\Transport\Http\HttpClientInterface;

final readonly class GitlabSource implements ProjectListProviderInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private ComposerFactory $composerFactory,
        private GitlabSourceConfig $config = new GitlabSourceConfig(),
    ) {}

    /**
     * @return \Generator<Project>
     */
    public function getComposerProjects(): \Generator
    {
        $page = 1;
        $membership = $this->config->membership ? 'true' : 'false';

        while (true) {
            /** @var list<array{id: int, name_with_namespace: string, default_branch: string}> $data */
            $data = $this->httpClient->get(
                "/projects?membership={$membership}&per_page={$this->config->perPage}&page={$page}"
            );
            if ([] === $data) {
                break;
            }

            foreach ($data as $project) {
                $branch = $this->config->defaultBranch ?? $project['default_branch'];
                $projectId = $project['id'];
                if ($this->hasComposerJson($projectId, $branch)) {
                    yield new Project(
                        (string) $projectId,
                        $project['name_with_namespace'],
                        $this->getComposerModel($projectId, $branch),
                    );
                }
            }

            ++$page;
        }
    }

    /**
     * @return array<mixed, mixed>
     */
    public function getData(int $projectId, string $fileExtension, string $branch, bool $ignoreNotFound): array
    {
        try {
            $fileResponse = $this->httpClient->get("projects/{$projectId}/repository/files/composer.{$fileExtension}?ref={$branch}");
        } catch (HttpException $e) {
            if ($ignoreNotFound && 404 === $e->getStatusCode()) {
                return [];
            }

            throw $e;
        }

        $content = isset($fileResponse['content']) && is_string($fileResponse['content']) ? $fileResponse['content'] : '';
        $json = base64_decode($content, true);
        if (!is_string($json)) {
            throw new InvalidComposerFileException("Invalid base64 in composer.{$fileExtension}");
        }

        $jsonData = json_decode($json, true);
        if (!is_array($jsonData)) {
            throw new InvalidComposerFileException("Invalid JSON in file composer.{$fileExtension}");
        }

        return $jsonData;
    }

    private function hasComposerJson(int $projectId, string $branch): bool
    {
        $httpCode = $this->httpClient->getStatusCode("projects/{$projectId}/repository/files/composer.json?ref={$branch}");

        return match ($httpCode) {
            200 => true,
            404 => false,
            default => throw new HttpException("HTTP error: {$httpCode}", $httpCode),
        };
    }

    private function getComposerModel(int $projectId, string $branch): Composer
    {
        return $this->composerFactory->create(
            $this->getData($projectId, 'json', $branch, false),
            $this->getData($projectId, 'lock', $branch, true),
        );
    }
}
