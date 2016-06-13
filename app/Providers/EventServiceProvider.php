<?php namespace Coyote\Providers;

use Coyote\Events\FirewallWasDeleted;
use Coyote\Events\FirewallWasSaved;
use Coyote\Events\UserWasSaved;
use Coyote\Listeners\BindRouteDefaultModel;
use Coyote\Listeners\FlushFirewallCache;
use Coyote\Listeners\FlushUserCache;
use Coyote\Listeners\SendLockoutEmail;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Routing\Events\RouteMatched;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        RouteMatched::class => [BindRouteDefaultModel::class],
        UserWasSaved::class => [FlushUserCache::class],
        Lockout::class => [SendLockoutEmail::class],
        FirewallWasSaved::class => [FlushFirewallCache::class],
        FirewallWasDeleted::class => [FlushFirewallCache::class]
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        'Coyote\Listeners\PageListener',
        'Coyote\Listeners\PostListener',
        'Coyote\Listeners\TopicListener',
        'Coyote\Listeners\JobListener',
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher $events
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);

        //
    }
}
