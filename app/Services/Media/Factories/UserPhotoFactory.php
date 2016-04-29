<?php

namespace Coyote\Services\Media\Factories;

use Coyote\Services\Media\MediaInterface;
use Coyote\Services\Media\UserPhoto;

class UserPhotoFactory extends AbstractFactory
{
    /**
     * @return MediaInterface
     */
    public function getMedia() : MediaInterface
    {
        return new UserPhoto($this);
    }
}
