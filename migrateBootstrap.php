<?php

function sourceCodeFiles(string $dir, &$results = []): array
{
    foreach (\scanDir($dir) as $child) {
        $path = \realPath($dir . DIRECTORY_SEPARATOR . $child);
        if (is_dir($path)) {
            if (\in_array($child, ['.', '..'])) {
                continue;
            }
            if (\in_array(basename($path), ['node_modules', 'vendor', 'neon', 'storage', '.idea', '.git', '.github'])) {
                continue;
            }
            if (\in_array($path, ['/var/www/public/js', '/var/www/public/css'])) {
                continue;
            }
            sourceCodeFiles($path, $results);
        } else {
            $ext = \pathInfo($path, PATHINFO_EXTENSION);
            if (basename($path) === basename(__FILE__)) {
                continue;
            }
            if (\in_array($ext, ['php', 'html', 'js', 'ts', 'twig', 'css', 'scss', 'vue'])) {
                $results[] = $path;
            }
        }
    }

    return $results;
}

function migrateFile(string $content): string
{
    $renames = [
        'float-left'          => 'float-start',
        'float-right'         => 'float-end',
        'text-left'           => 'text-start',
        'text-right'          => 'text-end',
        'dropdown-menu-left'  => 'dropdown-menu-start',
        'dropdown-menu-right' => 'dropdown-menu-end',
        'no-gutters'          => 'g-0',
        'font-weight-bold'    => 'fw-bold',
    ];
    $float = \preg_replace(
        \array_map(fn($key) => "/\b$key\b/", \array_keys($renames)),
        \array_values($renames),
        $content);
    return \preg_replace_callback(
        '/\b[mp][rl]-(xs-|sm-|md-|lg-|xl-)?(auto|\d+)\b/',
        function (array $match) {
            $string = $match[0];
            return \str_replace(['ml', 'mr', 'pl', 'pr'], ['ms', 'me', 'ps', 'pe'], $string);
        },
        $float);
}

$files = sourceCodeFiles(__DIR__);
\sort($files);

foreach ($files as $file) {
    $content = file_get_contents($file);
    $migrateFile = migrateFile($content);
    if ($content !== $migrateFile) {
        \file_put_contents($file, $migrateFile);
    }
}
