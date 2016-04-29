<?php

namespace Coyote\Services\Thumbnail;

use Coyote\Services\Thumbnail\Objects\ObjectInterface;
use Folklore\Image\ImageManager;

abstract class Proxy implements ThumbnailInterface
{
    /**
     * @var ImageManager
     */
    protected $imageManager;

    /**
     * @var ObjectInterface
     */
    protected $object;

    /**
     * @param ImageManager $imageManager
     */
    public function __construct(ImageManager $imageManager)
    {
        $this->imageManager = $imageManager;
    }

    /**
     * @param ObjectInterface $object
     * @return $this
     */
    public function setObject(ObjectInterface $object)
    {
        $this->object = $object;
        return $this;
    }

    /**
     * @param string $path
     * @return mixed
     */
    abstract public function make($path);
}
