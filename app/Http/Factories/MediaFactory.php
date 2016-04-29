<?php

namespace Coyote\Http\Factories;

use Coyote\Services\Media\Factories\AbstractFactory;

trait MediaFactory
{
    /**
     * @param string $factory
     * @return AbstractFactory
     */
    private function getMediaFactory($factory)
    {
        return app('media.' . $factory);
    }
}
