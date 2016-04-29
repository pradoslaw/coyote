<?php

namespace Coyote\Services\Media\Factories;

use Coyote\Services\Media\Logo;
use Coyote\Services\Media\MediaInterface;
// don't remove below line
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
