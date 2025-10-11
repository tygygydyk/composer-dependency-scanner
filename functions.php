<?php

function apiRequest($url, $token)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "PRIVATE-TOKEN: $token"
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo "cURL error: " . curl_error($ch) . "\n";
        return null;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true);
    }

    return null;
}

function getAllProjects($gitlabUrl, $token)
{
    $projects = [];
    $page = 1;
    while (true) {
        $url = "$gitlabUrl/api/v4/projects?membership=true&per_page=100&page=$page";
        $data = apiRequest($url, $token);
        if (empty($data)) break;

        $projects = array_merge($projects, $data);
        $page++;
    }

    return $projects;
}

function getComposerJson($gitlabUrl, $token, $projectId, $branch = 'master')
{
    $fileUrl = "$gitlabUrl/api/v4/projects/" . urlencode($projectId) . "/repository/files/composer.json/raw?ref=$branch";

    $ch = curl_init($fileUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "PRIVATE-TOKEN: $token"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        return json_decode($response, true);
    }

    return null;
}

function extractComposerInfo(array $composer, $internalNamespaces)
{
    $result = [
        'php_version' => $composer['require']['php'] ?? 'N/A',
        'symfony' => [],
        'internal_namespaces' => []
    ];

    foreach (($composer['require'] ?? []) as $package => $version) {
        if (strpos($package, 'symfony/') === 0) {
            $result['symfony'][$package] = $version;
        }
        $ns = explode('/', $package)[0];
        if (in_array($ns, $internalNamespaces)) {
            $result['internal_namespaces'][$package] = $version;
        }
    }

    return $result;
}
