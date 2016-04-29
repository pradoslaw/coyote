<?php

namespace Coyote\Http\Factories;

trait ThumbnailFactory
{
    /**
     * @return \Coyote\Services\Thumbnail\Factory
     */
    private function getThumbnailFactory()
    {
        return app('thumbnail');
    }
}
