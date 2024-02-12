<?php

namespace Coyote\Listeners;

use Coyote\Events\UserDeleted;
use Coyote\Events\UserSaved;
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
     * @param  UserSaved  $event
     */
    public function flushCache(UserSaved $event)
    {
        $this->cache->tags('permissions')->forget('permission:' . $event->user->id);
        $this->cache->tags('forum-order')->forget('forum-order:' . $event->user->id);
    }

    public function index(UserSaved $event)
    {
        $user = $event->user;

        dispatch_sync(function () use ($user) {
            (new Crawler())->index($user);
        });
    }

    public function delete(UserDeleted $event)
    {
        $user = (new User())->forceFill($event->user);

        dispatch_sync(function () use ($user) {
            (new Crawler())->delete($user);
        });
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            UserSaved::class,
            'Coyote\Listeners\UserSubscriber@flushCache'
        );

        $events->listen(
            UserSaved::class,
            'Coyote\Listeners\UserSubscriber@index'
        );

        $events->listen(
            UserDeleted::class,
            'Coyote\Listeners\UserSubscriber@delete'
        );
    }
}
