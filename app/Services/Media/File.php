<?php

namespace Coyote\Services\Media;

use Illuminate\Contracts\Filesystem\Filesystem;
use Coyote\Services\Thumbnail\Factory as Thumbnail;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class File implements MediaInterface
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
     * @param Thumbnail $thumbnail
     */
    public function __construct(Filesystem $filesystem, Thumbnail $thumbnail)
    {
        $this->filesystem = $filesystem;
        $this->thumbnail = $thumbnail;

        if (empty($this->directory)) {
            $this->directory = strtolower(class_basename($this));
        }
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
     * @return Url
     */
    public function url()
    {
        return new Url($this->thumbnail, $this);
    }

    /**
     * @return mixed
     */
    public function get()
    {
        return $this->filesystem->get($this->relative());
    }

    /**
     * @return int
     */
    public function size()
    {
        return $this->filesystem->size($this->relative());
    }

    /**
     * @param UploadedFile $uploadedFile
     * @return MediaInterface
     */
    public function upload(UploadedFile $uploadedFile)
    {
        $this->setName($uploadedFile->getClientOriginalName());
        $this->setFilename($this->getUniqueName($uploadedFile->getClientOriginalExtension()));
        $this->put(file_get_contents($uploadedFile->getRealPath()));

        return $this;
    }

    /**
     * @param mixed $content
     * @return MediaInterface
     */
    public function put($content)
    {
        $this->setName($this->getHumanName('png'));
        $this->setFilename($this->getUniqueName('png'));
        $this->filesystem->put($this->relative(), $content);

        return $this;
    }

    /**
     * @return bool
     */
    public function delete()
    {
        return $this->filesystem->delete($this->relative());
    }

    /**
     * Return relative path. Example: maps/1234.jpg
     *
     * @return string
     */
    public function relative()
    {
        return $this->directory . '/' . $this->filename;
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
