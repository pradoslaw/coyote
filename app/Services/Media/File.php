<?php

namespace Coyote\Services\Media;

use Illuminate\Contracts\Filesystem\Filesystem;

abstract class File implements MediaInterface
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
     * @return string
     */
    public function url()
    {
        $public = implode('/', array_diff(explode('/', $this->root()), explode('/', public_path())));

        return cdn($public . '/' . $this->relative());
    }

    /**
     * @return string
     */
    public function path()
    {
        return $this->root() . '/' . $this->relative();
    }

    /**
     * @param mixed $content
     */
    public function put($content)
    {
        $this->filesystem->put($this->relative(), $content);
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
     * @return string
     */
    protected function relative()
    {
        return $this->directory . '/' . $this->filename;
    }

    /**
     * @return string
     */
    protected function root()
    {
        $default = config('filesystems.default');
        return config("filesystems.disks.$default.root");
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
        return $this->getFilename();
    }
}
