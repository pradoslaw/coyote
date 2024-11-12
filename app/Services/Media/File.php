<?php
namespace Coyote\Services\Media;

use Coyote\Services\Media\Filters\Thumbnail;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\File as LaravelFile;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypes;

abstract class File
{
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

    public function __construct(private Filesystem $filesystem, private ImageWizard $wizard)
    {
        if (empty($this->directory)) {
            $this->directory = strtolower(class_basename($this));
        }
    }

    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * @return string|null
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
        return (new Url($this))->secure($secure);
    }

    /**
     * Return full path (example: /var/www/makana.pl/storage/uploads/maps/12345.jpg)
     *
     * @param string|null $filename
     * @return string
     */
    public function path($filename = null)
    {
        return $filename ?: $this->filename;
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

    public function upload(UploadedFile $uploadedFile)
    {
        $this->uploadFile($uploadedFile->getClientOriginalName(), $uploadedFile);
        return $this;
    }

    public function uploadFile(string $name, $uploadedFile): void
    {
        $this->setName($name);
        $this->setFilename($this->filesystem->putFile($this->directory, $uploadedFile, 'public'));
    }

    public function put($content)
    {
        $tmpFilePath = sys_get_temp_dir() . '/' . Str::uuid()->toString();
        file_put_contents($tmpFilePath, $content);

        $tmpFile = new LaravelFile($tmpFilePath);

        $this->setName($tmpFile->getFilename());

        $path = $this->filesystem->putFile(
            $this->directory,
            $tmpFile,
            'public',
        );

        $this->setFilename($path);

        return $this;
    }

    public function __toString(): string
    {
        return (string)$this->getFilename();
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

    protected function applyFilter(Thumbnail $thumbnail): void
    {
        $this->filesystem->put($this->path(), $this->wizard->resizedImage($thumbnail, $this->get()));
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
