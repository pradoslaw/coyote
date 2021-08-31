<?php

class Preloader
{
    private static int $count = 0;

    private array $paths;

    private array $fileMap;

    public function __construct(string ...$paths)
    {
        $this->paths = $paths;

        $classMap = [];
        $autoload = __DIR__ . '/vendor/composer/autoload_classmap.php';

        if (file_exists($autoload)) {
            $classMap = require $autoload;
        }

        $this->fileMap = array_flip($classMap);
    }

    public function paths(string ...$paths): Preloader
    {
        $this->paths = array_merge(
            $this->paths,
            $paths
        );

        return $this;
    }

    public function load(): void
    {
        // We'll loop over all registered paths
        // and load them one by one
        foreach ($this->paths as $path) {
            $this->loadPath(rtrim($path, '/'));
        }

        $count = self::$count;

        echo "[Preloader] Preloaded {$count} classes" . PHP_EOL;
    }

    private function loadPath(string $path): void
    {
        // If the current path is a directory,
        // we'll load all files in it
        if (is_dir($path)) {
            $this->loadDir($path);

            return;
        }

        // Otherwise we'll just load this one file
        $this->loadFile($path);
    }

    private function loadDir(string $path): void
    {
        $handle = opendir($path);

        // We'll loop over all files and directories
        // in the current path,
        // and load them one by one
        while ($file = readdir($handle)) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }

            $this->loadPath("{$path}/{$file}");
        }

        closedir($handle);
    }

    private function loadFile(string $path): void
    {
        // We resolve the classname from composer's autoload mapping
        $class = $this->fileMap[$path] ?? null;

        if (!$class) {
            return;
        }

        // Finally we require the path,
        // causing all its dependencies to be loaded as well
        @opcache_compile_file($path);

        self::$count++;

        echo "[Preloader] Preloaded `{$class}`" . PHP_EOL;
    }
}
(new Preloader())
//    ->paths(__DIR__ . '/vendor/elasticsearch')
//    ->paths(__DIR__ . '/vendor/ezyang')
//    ->paths(__DIR__ . '/vendor/erusev')
//    ->paths(__DIR__ . '/vendor/graylog2')
//    ->paths(__DIR__ . '/vendor/guzzlehttp')
//    ->paths(__DIR__ . '/vendor/fideloper')
//    ->paths(__DIR__ . '/vendor/filp')
//    ->paths(__DIR__ . '/vendor/freelancehunt')
//    ->paths(__DIR__ . '/vendor/jenssegers')
//    ->paths(__DIR__ . '/vendor/symfony')
//    ->paths(__DIR__ . '/vendor/laravel/passport')
//    ->paths(__DIR__ . '/vendor/laravel/socialite')
//    ->paths(__DIR__ . '/vendor/laravel/framework')
//    ->paths(__DIR__ . '/vendor/laravelcollective')
//    ->paths(__DIR__ . '/vendor/lcobucci')
//    ->paths(__DIR__ . '/vendor/league')
//    ->paths(__DIR__ . '/vendor/nesbot')
//    ->paths(__DIR__ . '/vendor/predis')
//    ->paths(__DIR__ . '/vendor/monolog')
//    ->paths(__DIR__ . '/vendor/sentry')
//    ->paths(__DIR__ . '/vendor/twig')
//    ->paths(__DIR__ . '/vendor/vlucas')
//    ->paths(__DIR__ . '/vendor/swiftmailer')
    ->load();
