<?php

namespace Coyote\Thumbnail\Objects;

use Imagine\Image;

/**
 * Class Object
 * @package Coyote\Thumbnail\Objects
 */
abstract class Object implements ObjectInterface
{
    /**
     * @var int
     */
    protected $width;

    /**
     * @var int
     */
    protected $height;

    /**
     * @return Image\Box
     */
    public function getBox()
    {
        return new Image\Box($this->width, $this->height);
    }

    /**
     * @return string
     */
    public function getInterface()
    {
        return Image\ImageInterface::THUMBNAIL_OUTBOUND;
    }
}