<?php

namespace Coyote\Services\Media;

use Coyote\Services\Media\Factories\AbstractFactory as MediaFactory;

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
     * @var string
     */
    protected $downloadUrl;

    /**
     * @var MediaFactory
     */
    protected $factory;

    /**
     * @param MediaFactory $factory
     */
    public function __construct(MediaFactory $factory)
    {
        $this->factory = $factory;

        if (empty($this->directory)) {
            $this->directory = strtolower(class_basename($this));
        }
    }

    /**
     * @return MediaFactory
     */
    public function getFactory()
    {
        return $this->factory;
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
     * @return string
     */
    public function url()
    {
        if ($this->downloadUrl && !$this->isImage()) {
            return $this->downloadUrl;
        }

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
        $this->factory->getFilesystem()->put($this->relative(), $content);
    }

    /**
     * @return mixed
     */
    public function get()
    {
        return $this->factory->getFilesystem()->get($this->relative());
    }

    /**
     * @return int
     */
    public function size()
    {
        return $this->factory->getFilesystem()->size($this->relative());
    }

    /**
     * @return bool
     */
    public function isImage()
    {
        return in_array(pathinfo($this->getFilename(), PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']);
    }

    /**
     * @return bool
     */
    public function delete()
    {
        return $this->factory->getFilesystem()->delete($this->relative());
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
     * @return string
     */
    public function __toString()
    {
        return $this->getFilename();
    }
}
