<?php
namespace Coyote\Services\Media;

use Illuminate\Contracts\Container\Container;

class Factory
{
    public function __construct(private Container $app)
    {
    }

    public function make(string $type, array $options = []): File
    {
        $class = $this->classNameByType($type);
        $fileSystem = $this->app['filesystem']->disk(config('filesystems.default'));
        $imageWizard = $this->app[ImageWizard::class];
        $media = new $class($fileSystem, $imageWizard);
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
}
