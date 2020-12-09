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
     * Make thumbnail and return full url.
     *
     * @param string $template
     * @return string|null
     */
    public function thumbnail($template)
    {
        if (!$this->file->getFilename()) {
            return null;
        }

        $thumbnailPath = $this->thumbnailPath($template);

        if (!$this->file->getFilesystem()->exists($thumbnailPath)) {
            $class = config("imagecache.templates.$template");
            $filter = new $class;

            $image = $this->imageManager->make($this->file->get());

            $this->file->getFilesystem()->put($thumbnailPath, $image->filter($filter)->encode());
        }

        return $this->file->getFilesystem()->url($thumbnailPath);
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

    /**
     * @param string $template
     * @return string
     */
    protected function thumbnailPath($template)
    {
        $pathinfo = pathinfo($this->file->getFilename());

        return ltrim($pathinfo['dirname'] . '/' . $pathinfo['filename'] . '-' . $template . '.' . $pathinfo['extension'], '/');
    }
}
