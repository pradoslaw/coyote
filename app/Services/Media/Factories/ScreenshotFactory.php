<?php

namespace Coyote\Services\Media\Factories;

use Coyote\Services\Media\MediaInterface;
use Coyote\Services\Media\Screenshot;

class ScreenshotFactory extends AbstractFactory
{
    /**
     * @return MediaInterface
     */
    public function getMedia() : MediaInterface
    {
        return new Screenshot($this);
    }
}
