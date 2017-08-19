<?php

namespace Coyote\Providers;

use Illuminate\Support\ServiceProvider;
use \Illuminate\Contracts\Broadcasting\Factory;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /** @var \Illuminate\Broadcasting\Broadcasters\Broadcaster $broadcast */
        $broadcast = $this->app[Factory::class];

        $broadcast->routes();
    }
}
