<?php

class Preloader
{
    private static int $count = 0;

    private array $fileMap;

    public function __construct()
    {
        $classMap = require __DIR__ . '/vendor/composer/autoload_classmap.php';

        $this->fileMap = array_flip($classMap);
    }

    public function load(): void
    {
        foreach ($this->fileMap as $path => $_) {
            $this->loadFile(rtrim($path, '/'));
        }

        $count = self::$count;

        echo "[Preloader] Preloaded {$count} classes" . PHP_EOL;
    }

    private function loadFile(string $path): void
    {
        $class = $this->fileMap[$path] ?? null;

        opcache_compile_file($path);

        self::$count++;

        echo "[Preloader] Preloaded `{$class}`" . PHP_EOL;
    }
}

(new Preloader())
    ->load();
