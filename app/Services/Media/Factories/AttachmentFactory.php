<?php

namespace Coyote\Services\Media\Factories;

use Coyote\Services\Media\Attachment;
use Coyote\Services\Media\MediaInterface;

class AttachmentFactory extends AbstractFactory
{
    /**
     * @return MediaInterface
     */
    public function getMedia() : MediaInterface
    {
        return new Attachment($this);
    }
}
