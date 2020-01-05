<?php

namespace Coyote\Services\Media;

use Illuminate\Contracts\Filesystem\Filesystem;
use Intervention\Image\Filters\FilterInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Intervention\Image\ImageManager;
use Symfony\Component\Mime\MimeTypes;

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
    protected $downloadUrl;

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
     * @return string
     */
    public function getDownloadUrl()
    {
        return $this->downloadUrl;
    }

    /**
     * @param string $downloadUrl
     * @return $this
     */
    public function setDownloadUrl($downloadUrl)
    {
        $this->downloadUrl = $downloadUrl;

        return $this;
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
        return ($filename ?: $this->relative());
    }

    /**
     * @return string
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

        $this->filesystem->put($this->relative(), file_get_contents($uploadedFile->getRealPath()), 'public');

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

        $this->filesystem->put($this->relative(), $content, 'public');

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
     * Return relative path. Example: attachment/1234.jpg
     *
     * @return string
     */
    public function relative()
    {
        return $this->directory() . '/' . $this->filename;
    }

    /**
     * @return bool
     */
    public function isImage()
    {
        return in_array(pathinfo($this->getFilename(), PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']);
    }

    /**
     * @return string|null
     */
    public function getMime(): ?string
    {
        $mimes = (new MimeTypes())->getMimeTypes(pathinfo($this->getFilename(), PATHINFO_EXTENSION));

        return $mimes[0] ?? null;
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

    /**
     * @return string
     */
    protected function directory()
    {
        if (strlen($this->filename) !== 17) {
            return $this->directory;
        }

        $timestamp = hexdec(substr($this->filename, 0, 8));
        // as of 15th of Jan, we decided to put files into subdirectories
        if ($timestamp < 1484438400) {
            return $this->directory;
        }

        return $this->directory . '/' . substr($this->filename, 0, 2);
    }
}
