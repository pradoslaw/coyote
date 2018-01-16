<?php

namespace Coyote\Services\Thumbnail\Objects;

interface ObjectInterface
{
    /**
     * @return mixed
     */
    public function getWidth();

    /**
     * @return mixed
     */
    public function getHeight();
}
