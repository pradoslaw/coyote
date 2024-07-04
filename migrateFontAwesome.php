<?php

function sourceCodeFiles(string $dir, &$results = []): array
{
    foreach (\scanDir($dir) as $child) {
        $path = \realPath($dir . DIRECTORY_SEPARATOR . $child);
        if (is_dir($path)) {
            if (\in_array($child, ['.', '..'])) {
                continue;
            }
            if (\in_array(basename($path), ['node_modules', 'vendor', 'storage', '.idea', '.git', '.github'])) {
                continue;
            }
            if (\in_array($path, ['/var/www/public/js', '/var/www/public/css'])) {
                continue;
            }
            sourceCodeFiles($path, $results);
        } else {
            $ext = \pathInfo($path, PATHINFO_EXTENSION);
            if (\in_array($ext, ['php', 'html', 'js', 'ts', 'twig', 'css', 'scss', 'vue'])) {
                $results[] = $path;
            }
        }
    }

    return $results;
}

$files = sourceCodeFiles(__DIR__);
\sort($files);

$replace = \json_decode(\file_get_contents('migrateFontAwesome.json'), true);

foreach ($files as $file) {
    $content = file_get_contents($file);
    $replaced = \preg_replace_callback('/(fa|\$fa-var)-([a-z0-9-]+)/',
        function (array $match) use ($replace) {
            $syntax = $match[1];
            $iconName = $match[2];
            if (\array_key_exists($iconName, $replace)) {
                return $syntax . '-' . $replace[$iconName];
            }
            return $match[0];
        },
        $content);
    \file_put_contents($file, $replaced);
}
