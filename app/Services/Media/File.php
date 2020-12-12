<?php

namespace Coyote\Services\Media;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Intervention\Image\Filters\FilterInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Intervention\Image\ImageManager;
use Symfony\Component\Mime\MimeTypes;
use Illuminate\Http\File as LaravelFile;

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
     * @param mixed $content
     * @return MediaInterface
     */
    public function put($content)
    {
        $tmpFilePath = sys_get_temp_dir() . '/' . Str::uuid()->toString();
        file_put_contents($tmpFilePath, $content);

        $tmpFile = new LaravelFile($tmpFilePath);

        $this->setName($tmpFile->getFilename());

        $path = $this->filesystem->putFile(
            $this->directory,
            $tmpFile,
            'public'
        );

        $this->setFilename($path);

        return $this;
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
     * @return string|null
     */
    public function getMime(): ?string
    {
        $mimes = (new MimeTypes())->getMimeTypes(pathinfo($this->getFilename(), PATHINFO_EXTENSION));

        return $mimes[0] ?? null;
    }
}
