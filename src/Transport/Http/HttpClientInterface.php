<?php

namespace Tygygydyk\ComposerDependencyScanner\Transport\Http;

use Tygygydyk\ComposerDependencyScanner\Exception\HttpException;

interface HttpClientInterface
{
    /**
     * @return array<mixed>
     *
     * @throws HttpException When request fails (network error) or response is 4xx/5xx (statusCode 0 = no response received)
     */
    public function get(string $url): array;

    /**
     * @throws HttpException When request fails (network error) or response is not 2xx (statusCode 0 = no response received)
     */
    public function getStatusCode(string $url): int;
}
