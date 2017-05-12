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
     * @var null|bool
     */
    protected $secure;

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
     * @param bool|null $flag
     * @return $this
     */
    public function secure($flag)
    {
        $this->secure = $flag;

        return $this;
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

        return $this->thumbnail->url($template)->make((string) $this);
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
    protected function publicPath()
    {
        return implode('/', array_diff(explode('/', $this->file->root()), explode('/', public_path())));
    }

    /**
     * @return string
     */
    private function makeUrl()
    {
        if ($this->file->getDownloadUrl() && !$this->file->isImage()) {
            return $this->file->getDownloadUrl();
        }

        return cdn($this->publicPath() . '/' . $this->file->relative(), $this->secure);
    }
}
