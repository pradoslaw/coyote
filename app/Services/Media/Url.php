<?php

namespace Coyote\Services\Media;

use Coyote\Services\Thumbnail\Factory as Thumbnail;
use Coyote\Services\Thumbnail\Objects\ObjectInterface;

class Url
{
    /**
     * @var Thumbnail
     */
    protected $thumbnail;

    /**
     * @var File
     */
    protected $file;

    /**
     * @param Thumbnail $thumbnail
     * @param File $file
     */
    public function __construct(Thumbnail $thumbnail, File $file)
    {
        $this->thumbnail = $thumbnail;
        $this->file = $file;
    }

    /**
     * Make thumbnail and return full url.
     *
     * @param ObjectInterface $template
     * @return string|null
     */
    public function thumbnail($template)
    {
        if (!$this->file->getFilename()) {
            return null;
        }

        return cdn($this->thumbnail->url($template)->make((string) $this));
    }

    /**
     * Return full path (example: /var/www/makana.pl/storage/uploads/maps/12345.jpg)
     *
     * @param string|null $filename
     * @return string
     */
    public function path($filename = null)
    {
        return $this->rootPath() . '/' . ($filename ?: $this->file->relative());
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->makeUrl();
    }

    /**
     * @return string
     */
    protected function rootPath()
    {
        $default = config('filesystems.default');
        return config("filesystems.disks.$default.root");
    }

    /**
     * @return string
     */
    protected function publicPath()
    {
        return implode('/', array_diff(explode('/', $this->rootPath()), explode('/', public_path())));
    }

    /**
     * @return string
     */
    private function makeUrl()
    {
        return cdn($this->publicPath() . '/' . $this->file->relative());
    }
}
