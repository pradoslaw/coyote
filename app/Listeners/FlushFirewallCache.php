<?php

namespace Coyote\Listeners;

use Coyote\Services\Firewall\Rules;
use Illuminate\Contracts\Cache\Repository as Cache;

class FlushFirewallCache
{
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @param Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Handle the event.
     */
    public function handle()
    {
        $this->cache->forget(Rules::CACHE_KEY);
    }
}
