<?php
namespace Coyote\Services\Media;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;

class Factory
{
    private ImageWizard $imageWizard;
    private FilesystemManager $manager;

    public static function get(): self
    {
        return app(self::class);
    }

    public function __construct(Container $app)
    {
        $this->imageWizard = $app[ImageWizard::class];
        $this->manager = $app['filesystem'];
    }

    public function userAvatar(?string $value): Photo
    {
        return $this->make('photo', ['file_name' => $value]);
    }

    public function make(string $type, array $options = []): File
    {
        $class = $this->classNameByType($type);
        $media = new $class($this->filesystem(), $this->imageWizard);
        foreach ($options as $name => $value) {
            $method = \camel_case('set_' . $name);
            if (\method_exists($media, $method)) {
                $media->$method($value);
            }
        }
        return $media;
    }

    private function classNameByType(string $type): string
    {
        $className = 'Coyote\Services\Media\\' . \ucfirst($type);
        if (class_exists($className)) {
            return $className;
        }
        throw new \InvalidArgumentException("Can't find $className class in media factory.");
    }

    private function filesystem(): Filesystem
    {
        return $this->manager->disk(\config('filesystems.default'));
    }
}
