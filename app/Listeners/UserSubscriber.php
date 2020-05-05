<?php

namespace Coyote\Listeners;

use Coyote\Events\UserDeleted;
use Coyote\Events\UserWasSaved;
use Coyote\Services\Elasticsearch\Crawler;
use Coyote\User;
use Illuminate\Contracts\Cache\Repository;

class UserSubscriber
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
    public function flushCache(UserWasSaved $event)
    {
        $this->cache->tags('menu-for-user')->forget('menu-for-user:' . $event->user->id);
        $this->cache->tags('permissions')->forget('permission:' . $event->user->id);
        $this->cache->tags('forum-order')->forget('forum-order:' . $event->user->id);
    }

    /**
     * @param UserWasSaved|UserDeleted $event
     */
    public function reindex($event)
    {
        $crawler = new Crawler();

        $event instanceof UserWasSaved ? $crawler->index($event->user) : $crawler->delete((new User)->forceFill($event->user));
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            UserWasSaved::class,
            'Coyote\Listeners\UserSubscriber@flushCache'
        );

        $events->listen(
            UserWasSaved::class,
            'Coyote\Listeners\UserSubscriber@reindex'
        );

        $events->listen(
            UserDeleted::class,
            'Coyote\Listeners\UserSubscriber@reindex'
        );
    }
}
