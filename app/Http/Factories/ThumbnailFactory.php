<?php

namespace Coyote\Http\Factories;

trait ThumbnailFactory
{
    /**
     * @return \Coyote\Services\Thumbnail\Thumbnail
     */
    private function getThumbnailFactory()
    {
        return app('thumbnail');
    }
}
