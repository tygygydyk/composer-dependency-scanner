<?php

namespace Tygygydyk\ComposerDependencyScanner\Transport\Http;

use Tygygydyk\ComposerDependencyScanner\Exception\HttpException;

/**
 * TODO make it more general when implementing github.
 *
 * @internal
 */
class GitlabHttpClient implements HttpClientInterface
{
    private string $apiUrl;

    public function __construct(
        string $url,
        private string $token,
    ) {
        $this->apiUrl = "{$url}/api/v4";
    }

    /**
     * @return array<mixed>
     */
    public function get(string $url): array
    {
        $ch = curl_init("{$this->apiUrl}/{$url}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "PRIVATE-TOKEN: {$this->token}",
        ]);

        $response = curl_exec($ch);
        if (0 !== curl_errno($ch)) {
            curl_close($ch);

            throw new HttpException('Request failed: '.curl_error($ch), 0);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $decoded = is_string($response) ? json_decode($response, true) : null;

            return is_array($decoded) ? $decoded : [];
        }

        throw new HttpException("HTTP error: {$httpCode}", $httpCode);
    }

    public function getStatusCode(string $url): int
    {
        $fullUrl = $this->apiUrl.'/'.ltrim($url, '/');
        $ch = curl_init($fullUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "PRIVATE-TOKEN: {$this->token}",
            ],
            CURLOPT_NOBODY => true,
        ]);

        curl_exec($ch);
        if (0 !== curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);

            throw new HttpException('Request failed: '.$error, 0);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode;
    }
}
