<?php

namespace Coyote\Listeners;

use Coyote\Events\UserWasSaved;
use Coyote\Repositories\Eloquent\FirewallRepository;
use Illuminate\Contracts\Cache\Repository;

class FlushFirewallCache
{
    /**
     * @var Repository
     */
    protected $cache;

    /**
     * @param Repository $cache
     */
    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Handle the event.
     *
     * @param  UserWasSaved  $event
     */
    public function handle()
    {
        $this->cache->forget(FirewallRepository::CACHE_KEY);
    }
}
