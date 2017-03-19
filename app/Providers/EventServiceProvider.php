<?php

namespace Coyote\Providers;

use Coyote\Events\FirewallWasDeleted;
use Coyote\Events\FirewallWasSaved;
use Coyote\Events\PaymentPaid;
use Coyote\Events\SuccessfulLogin;
use Coyote\Events\UserWasSaved;
use Coyote\Listeners\BoostJobOffer;
use Coyote\Listeners\ChangeImageUrl;
use Coyote\Listeners\ChangePaymentStatus;
use Coyote\Listeners\FlushFirewallCache;
use Coyote\Listeners\FlushUserCache;
use Coyote\Listeners\MicroblogListener;
use Coyote\Listeners\SaveLocationsInJobPreferences;
use Coyote\Listeners\SendLockoutEmail;
use Coyote\Listeners\SendSuccessfulLoginEmail;
use Coyote\Listeners\SetupWikiLinks;
use Coyote\Listeners\WikiListener;
use Coyote\Listeners\PageListener;
use Coyote\Listeners\PostListener;
use Coyote\Listeners\TopicListener;
use Coyote\Listeners\JobListener;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Mail\Events\MessageSending;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        UserWasSaved::class => [FlushUserCache::class, SaveLocationsInJobPreferences::class],
        Lockout::class => [SendLockoutEmail::class],
        FirewallWasSaved::class => [FlushFirewallCache::class],
        FirewallWasDeleted::class => [FlushFirewallCache::class],
        SuccessfulLogin::class => [SendSuccessfulLoginEmail::class],
        MessageSending::class => [ChangeImageUrl::class]
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        PageListener::class,
        PostListener::class,
        TopicListener::class,
        JobListener::class,
        MicroblogListener::class,
        WikiListener::class,
        SetupWikiLinks::class
    ];

    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot()
    {
        // set high priority. we need to call this listener first.
        $this->app['events']->listen(PaymentPaid::class, ChangePaymentStatus::class, 1001);
        $this->app['events']->listen(PaymentPaid::class, BoostJobOffer::class, 1000);

        parent::boot();
    }
}
