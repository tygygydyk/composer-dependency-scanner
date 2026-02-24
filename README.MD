# Composer Dependency Scanner

Scans Composer dependencies across GitLab projects: lists your projects and returns data from `composer.json` / `composer.lock` (PHP version, dependencies, locked versions).

**Requirements:** PHP 8.2+, ext-curl

```bash
composer require tygygydyk/composer-dependency-scanner
```

## Usage

Entry point is `Client`. Inject a project list provider (e.g. GitLab). `getComposerProjects()` yields `Project` objects (`id`, `name`, `composer` with `php`, `dependencies`, `dependenciesDev`).

**Plain PHP:**

```php
use Tygygydyk\ComposerDependencyScanner\Client;use Tygygydyk\ComposerDependencyScanner\Factory\ComposerFactory;use Tygygydyk\ComposerDependencyScanner\ProjectProvider\Gitlab\GitlabSource;use Tygygydyk\ComposerDependencyScanner\ProjectProvider\Gitlab\GitlabSourceConfig;use Tygygydyk\ComposerDependencyScanner\Transport\Http\GitlabHttpClient;

$http = new GitlabHttpClient('https://gitlab.example.com', 'your-token');
$config = new GitlabSourceConfig(100, true, null);  // null = branch from API
$client = new Client(new GitlabSource($http, new ComposerFactory(), $config));

foreach ($client->getComposerProjects() as $project) {
    echo $project->name . ' — PHP ' . $project->composer->php . "\n";
}
```

## Symfony

**Parameters** (use `.env` for secrets):

```yaml
parameters:
    gitlab.url: '%env(GITLAB_URL)%'
    gitlab.token: '%env(GITLAB_TOKEN)%'
    gitlab.scanner.per_page: 100
    gitlab.scanner.membership: true
    gitlab.scanner.default_branch: null
```

**Services** (`config/services.yaml`):

```yaml
services:
    Tygygydyk\ComposerDependencyScanner\Transport\Http\GitlabHttpClient:
        arguments: { $url: '%gitlab.url%', $token: '%gitlab.token%' }
    
    # Or just Tygygydyk\ComposerDependencyScanner\ProjectProvider\Gitlab\GitlabSourceConfig: ~
    # for default params.
    Tygygydyk\ComposerDependencyScanner\ProjectProvider\Gitlab\GitlabSourceConfig:
        arguments:
            $perPage: '%gitlab.scanner.per_page%'
            $membership: '%gitlab.scanner.membership%'
            $defaultBranch: '%gitlab.scanner.default_branch%'

    Tygygydyk\ComposerDependencyScanner\Factory\ComposerFactory: ~

    Tygygydyk\ComposerDependencyScanner\ProjectProvider\Gitlab\GitlabSource:
        arguments:
            $httpClient: '@Tygygydyk\ComposerDependencyScanner\Transport\Http\GitlabHttpClient'
            $composerFactory: '@Tygygydyk\ComposerDependencyScanner\Factory\ComposerFactory'
            $config: '@Tygygydyk\ComposerDependencyScanner\ProjectProvider\Gitlab\GitlabSourceConfig'

    Tygygydyk\ComposerDependencyScanner\Client:
        arguments:
            $projectListProvider: '@Tygygydyk\ComposerDependencyScanner\ProjectProvider\Gitlab\GitlabSource'
```

**.env:** `GITLAB_URL=https://gitlab.example.com`, `GITLAB_TOKEN=glpat-xxx`

Inject `Client` where needed; use `gitlab.scanner.default_branch: 'main'` to force one branch for all repos.

## Exceptions

- `HttpException` — request failed (network or 4xx/5xx). `getStatusCode()` is `0` when no response was received.
- `InvalidComposerFileException` — invalid JSON in composer.json / composer.lock.
