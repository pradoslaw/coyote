<?php

namespace Coyote\Services\Thumbnail\Objects;

/**
 * Class Photo
 * @package Coyote\Services\Thumbnail\Objects
 */
class Photo extends ObjectAbstract implements ObjectInterface
{
    /**
     * @var int
     */
    protected $width = 120;

    /**
     * @var int
     */
    protected $height = 120;
}
