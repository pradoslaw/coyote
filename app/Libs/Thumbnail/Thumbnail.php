<?php

namespace Coyote\Thumbnail;

use Coyote\Thumbnail\Objects\ObjectInterface;
use Image;

/**
 * Class Thumbnail
 * @package Coyote\Thumbnail
 */
class Thumbnail
{
    /**
     * @var ObjectInterface
     */
    private $object;

    /**
     * Thumbnail constructor.
     * @param ObjectInterface $object
     */
    public function __construct(ObjectInterface $object)
    {
        $this->object = $object;
    }

    /**
     * @param $path
     */
    public function make($path)
    {
        $thumbnail = Image::open($path)->thumbnail(
            $this->object->getBox(),
            $this->object->getInterface()
        );

        $thumbnail->save($path);
    }
}