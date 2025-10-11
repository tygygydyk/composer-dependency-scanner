<?php

$config = require 'config.php';
require 'functions.php';

$gitlabUrl = rtrim($config['gitlab_url'], '/');
$token = $config['access_token'];
$branch = $config['default_branch'];
$internalNamespaces = $config['internal_namespaces'];

$projects = getAllProjects($gitlabUrl, $token);
echo "Projects: " . count($projects) . "\n";
foreach ($projects as $project) {
    $composer = getComposerJson($gitlabUrl, $token, $project['id'], $branch);

    if ($composer) {
        $info = extractComposerInfo($composer, $internalNamespaces);

        echo "Project: {$project['name']} ({$project['path_with_namespace']})\n";
        echo "PHP Version: {$info['php_version']}\n";

        echo "Symfony Packages:\n";
        foreach ($info['symfony'] as $ns => $ver) {
            echo "    - {$ns}: {$ver}\n";
        }

        echo "Internal Packages:\n";
        foreach ($info['internal_namespaces'] as $ns => $ver) {
            echo "    - {$ns}: {$ver}\n";
        }

        echo str_repeat('-', 50) . "\n";
    }
}
