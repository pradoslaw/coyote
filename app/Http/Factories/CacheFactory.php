<?php

namespace Coyote\Http\Factories;

use Illuminate\Contracts\Cache\Repository;

trait CacheFactory
{
    /**
     * @return Repository
     */
    private function getCacheFactory()
    {
        return app(Repository::class);
    }
}
