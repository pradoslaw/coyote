<?php

namespace Coyote\Services\Thumbnail;

use Coyote\Services\Thumbnail\Objects\ObjectInterface;
use Folklore\Image\ImageManager;

class Factory
{
    /**
     * @var ImageManager
     */
    protected $imageManager;

    /**
     * @param ImageManager $imageManager
     */
    public function __construct(ImageManager $imageManager)
    {
        $this->imageManager = $imageManager;
    }

    /**
     * Return object that can create thumbnail file and replace original.
     *
     * @param ObjectInterface $object
     * @return ThumbnailInterface
     */
    public function file(ObjectInterface $object) : ThumbnailInterface
    {
        return (new File($this->imageManager))->setObject($object);
    }

    /**
     * Return object that can create URL to the thumbnail.
     *
     * @param ObjectInterface $object
     * @return ThumbnailInterface
     */
    public function url(ObjectInterface $object) : ThumbnailInterface
    {
        return (new Url($this->imageManager))->setObject($object);
    }
}
