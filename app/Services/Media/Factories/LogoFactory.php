<?php

namespace Coyote\Services\Media\Factories;

use Coyote\Services\Media\Logo;
use Coyote\Services\Media\MediaInterface;

class LogoFactory extends AbstractFactory
{
    /**
     * @return MediaInterface
     */
    public function getMedia() : MediaInterface
    {
        return new Logo($this);
    }
}
