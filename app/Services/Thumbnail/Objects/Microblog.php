<?php

namespace Coyote\Services\Thumbnail\Objects;

/**
 * @package Coyote\Services\Thumbnail\Objects
 */
class Microblog extends ObjectAbstract implements ObjectInterface
{
    /**
     * @var int
     */
    protected $width = 180;

    /**
     * @var int
     */
    protected $height = 180;
}
