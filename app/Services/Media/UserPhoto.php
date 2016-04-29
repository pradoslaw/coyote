<?php

namespace Coyote\Services\Media;

use Coyote\Services\Thumbnail\Objects\Photo;

class UserPhoto extends File
{
    protected $directory = 'photo';

    public function put($content)
    {
        parent::put($content);

        // after uploading file, we need to change size of the picture
        $this->factory->getThumbnail()->file(new Photo())->make($this->path());
    }
}
