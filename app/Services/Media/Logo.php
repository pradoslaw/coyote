<?php

namespace Coyote\Services\Media;

class Logo extends File
{
    public function put($content)
    {
        parent::put($content);

        // after uploading logo, we need to change size according to the Objects\Logo()
        $this->factory->getThumbnail()->file(new \Coyote\Services\Thumbnail\Objects\Logo())->make($this->path());
    }
}
