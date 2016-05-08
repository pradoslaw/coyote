<?php

namespace Coyote\Services\Media\Factories;

use Coyote\Services\Thumbnail\Factory as Thumbnail;
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
     * @var Thumbnail
     */
    protected $thumbnail;

    /**
     * @param Filesystem $filesystem
     * @param Thumbnail $thumbnail
     */
    public function __construct(Filesystem $filesystem, Thumbnail $thumbnail)
    {
        $this->filesystem = $filesystem;
        $this->thumbnail = $thumbnail;
    }

    /**
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * @return Thumbnail
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * @return MediaInterface
     */
    abstract public function getMedia() : MediaInterface;

    /**
     * Create the new media class based on options. This method is being used in model to initialize
     * appropriate object.
     *
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
     * @param string $content
     * @return MediaInterface
     */
    public function put($content)
    {
        $media = $this->getMedia();
        $media->setName($this->getHumanName('png'));
        $media->setFilename($this->getUniqueName('png'));
        $media->put($content);

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
    protected function getHumanName($extension)
    {
        return 'screenshot-' . date('YmdHis') . '.' . $extension;
    }
}
