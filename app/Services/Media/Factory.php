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
        $class = $this->getClass($type);
        if (!class_exists($class)) {
            throw new \InvalidArgumentException("Can't find $class class in media factory.");
        }
        $fileSystem = $this->app['filesystem']->disk(config('filesystems.default'));
        $imageWizard = $this->app[ImageWizard::class];
        $media = new $class($fileSystem, $imageWizard);
        return $this->setDefaultOptions($media, $options);
    }

    protected function setDefaultOptions(File $media, array $options = []): File
    {
        if ($options) {
            foreach ($options as $name => $value) {
                $method = camel_case('set_' . $name);
                if (method_exists($media, $method)) {
                    $media->$method($value);
                }
            }
        }
        return $media;
    }

    private function getClass(string $type): string
    {
        return __NAMESPACE__ . '\\' . ucfirst($type);
    }
}
