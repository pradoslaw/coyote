<?php

namespace Coyote\Http\Factories;

trait CacheFactory
{
    /**
     * @return \Illuminate\Contracts\Cache\Repository
     */
    private function getCacheFactory()
    {
        return app(\Illuminate\Contracts\Cache\Repository::class);
    }
}
