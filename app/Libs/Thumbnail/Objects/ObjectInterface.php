<?php

namespace Coyote\Thumbnail\Objects;

interface ObjectInterface
{
    /**
     * @return \Imagine\Image\Box
     */
    public function getBox();

    /**
     * @return \Imagine\Image\ImageInterface
     */
    public function getInterface();
}