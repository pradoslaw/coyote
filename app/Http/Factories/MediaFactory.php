<?php

namespace Coyote\Http\Factories;

use Coyote\Services\Media\Factory;

trait MediaFactory
{
    /**
     * @return Factory
     */
    private function getMediaFactory()
    {
        return app(Factory::class);
    }
}
