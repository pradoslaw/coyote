<?php

namespace Coyote\Services\Thumbnail;

use Coyote\Services\Thumbnail\Objects\ObjectInterface;

interface ThumbnailInterface
{
    /**
     * @param ObjectInterface $object
     * @return $this
     */
    public function setObject(ObjectInterface $object);

    /**
     * @param string $path
     * @return mixed
     */
    public function make($path);
}
