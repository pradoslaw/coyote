<?php

namespace Coyote\Services\Thumbnail\Objects;

/**
 * Class Object
 * @package Coyote\Services\Thumbnail\Objects
 */
abstract class ObjectAbstract implements ObjectInterface
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
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }
}
