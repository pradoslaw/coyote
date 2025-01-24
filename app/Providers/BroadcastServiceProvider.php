<?php
namespace Coyote\Providers;

use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Contracts\Broadcasting\Factory;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /** @var BroadcastManager $broadcast */
        $broadcast = $this->app[Factory::class];
        $broadcast->routes();
    }
}
