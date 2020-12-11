<?php

namespace Coyote\Services\Media;

use Intervention\Image\ImageManager;

class Url
{
    /**
     * @var ImageManager
     */
    protected $imageManager;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var null|bool
     */
    protected $secure;

    /**
     * @param ImageManager $imageManager
     * @param File $file
     */
    public function __construct(ImageManager $imageManager, File $file)
    {
        $this->imageManager = $imageManager;
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
     * @return string
     */
    public function __toString()
    {
        return $this->makeUrl();
    }

    /**
     * @return string
     */
    private function makeUrl()
    {
        if (!$this->file->getFilename()) {
            return ''; // because __toString() requires string value
        }

        return $this->file->getFilesystem()->url($this->file->path());
    }
}
