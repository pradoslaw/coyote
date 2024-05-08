<?php

namespace Coyote\Services\Media;

use Illuminate\Contracts\Container\Container as App;

class Factory
{
    /**
     * @var App
     */
    protected $app;

    /**
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @param string $type
     * @param array $options
     * @return MediaInterface
     */
    public function make(string $type, array $options = []): MediaInterface
    {
        $class = $this->getClass($type);
        if (!class_exists($class, true)) {
            throw new \InvalidArgumentException("Can't find $class class in media factory.");
        }
        $fileSystem = $this->app['filesystem']->disk(config('filesystems.default'));
        $imageWizard = $this->app[ImageWizard::class];
        $media = new $class($fileSystem, $imageWizard);
        return $this->setDefaultOptions($media, $options);
    }

    /**
     * @param MediaInterface $media
     * @param array $options
     * @return MediaInterface
     */
    protected function setDefaultOptions(MediaInterface $media, array $options = []): MediaInterface
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

    /**
     * @param string $type
     * @return string
     */
    private function getClass(string $type): string
    {
        return __NAMESPACE__ . '\\' . ucfirst($type);
    }
}
