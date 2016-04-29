<?php

namespace Coyote\Services\Media\Factories;

use Coyote\Services\Media\MediaInterface;
use Illuminate\Contracts\Filesystem\Filesystem;
// don't remove below line
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class AbstractFactory
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @return MediaInterface
     */
    abstract public function getMedia() : MediaInterface;

    /**
     * @param array $options
     * @return MediaInterface
     */
    public function make(array $options = [])
    {
        $media = $this->getMedia();
        
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
     * @param UploadedFile $uploadedFile
     * @return MediaInterface
     */
    public function upload(UploadedFile $uploadedFile)
    {
        $media = $this->getMedia();
        $media->setName($uploadedFile->getClientOriginalName());
        $media->setFilename($this->getUniqueName($uploadedFile->getClientOriginalExtension()));
        $media->put(file_get_contents($uploadedFile->getRealPath()));

        return $media;
    }

    /**
     * @param $content
     * @return MediaInterface
     */
    public function put($content)
    {
        $media = $this->getMedia();
        $media->setName($this->getHumanName('png'));
        $media->setFilename($this->getUniqueName('png'));
        $media->put(file_get_contents('data://' . substr($content, 7)));

        return $media;
    }

    /**
     * @param string $extension
     * @return string
     */
    protected function getUniqueName($extension)
    {
        return uniqid() . '.' . strtolower($extension);
    }

    /**
     * @param string $extension
     * @return string
     */
    public function getHumanName($extension)
    {
        return 'screenshot-' . date('YmdHis') . '.' . $extension;
    }
}
