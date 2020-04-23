<?php

namespace Coyote\Listeners;

use Coyote\Events\UserWasSaved;
use Illuminate\Contracts\Cache\Repository;

// do not implement ShouldQueue interface. We need to flush cache immediately.
class FlushUserCache
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
    public function handle(UserWasSaved $event)
    {
        $this->cache->tags('menu-for-user')->forget('menu-for-user:' . $event->user->id);
        $this->cache->tags('permissions')->forget('permission:' . $event->user->id);
        $this->cache->tags('forum-order')->forget('forum-order:' . $event->user->id);
    }
}
