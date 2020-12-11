<?php

namespace Coyote\Services\Media;

use Illuminate\Contracts\Filesystem\Filesystem;
use Intervention\Image\Filters\FilterInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Intervention\Image\ImageManager;

abstract class File implements MediaInterface
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var ImageManager
     */
    protected $imageManager;

    /**
     * @var string
     */
    protected $directory;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @param Filesystem $filesystem
     * @param ImageManager $imageManager
     */
    public function __construct(Filesystem $filesystem, ImageManager $imageManager)
    {
        $this->filesystem = $filesystem;
        $this->imageManager = $imageManager;

        if (empty($this->directory)) {
            $this->directory = strtolower(class_basename($this));
        }
    }

    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param null|bool $secure
     * @return Url
     */
    public function url($secure = null)
    {
        return (new Url($this->imageManager, $this))->secure($secure);
    }

    /**
     * Return full path (example: /var/www/makana.pl/storage/uploads/maps/12345.jpg)
     *
     * @param string|null $filename
     * @return string
     */
    public function path($filename = null)
    {
        return ($filename ?: $this->filename);
    }

    /**
     * @return string
     */
    public function get()
    {
        return $this->filesystem->get($this->filename);
    }

    /**
     * @return int
     */
    public function size()
    {
        return $this->filesystem->size($this->filename);
    }

    /**
     * @param UploadedFile $uploadedFile
     * @return MediaInterface
     */
    public function upload(UploadedFile $uploadedFile)
    {
        $this->setName($uploadedFile->getClientOriginalName());

        $path = $this->filesystem->putFile($this->directory, $uploadedFile, 'public');
        $this->setFilename($path);

        return $this;
    }

    /**
     * @return bool
     */
    public function delete()
    {
        return $this->filesystem->delete($this->filename);
    }

    /**
     * @return bool
     */
    public function isImage()
    {
        return in_array(pathinfo($this->getFilename(), PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getFilename();
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return method_exists($this, camel_case($name));
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (!isset($this->{$name})) {
            throw new \InvalidArgumentException("Method $name does not exist in File class.");
        }

        return $this->{camel_case($name)}();
    }

    /**
     * @param FilterInterface $filter
     * @return \Intervention\Image\Image
     */
    protected function applyFilter(FilterInterface $filter)
    {
        $image = $this->imageManager->make($this->get());

        // save new image
        $this->filesystem->put($this->path(), $image->filter($filter)->encode());

        return $image;
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
