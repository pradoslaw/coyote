<?php

namespace Coyote\Providers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['view']->composer('*', function (View $view) {
            $this->registerPublicData();
            $this->registerWebSocket();

            $view->with('__public', json_encode($this->app['request']->attributes->all()));
        });
    }

    private function registerWebSocket()
    {
        if (config('services.ws.host') && $this->app['request']->user()) {
            $this->app['request']->attributes->set(
                'ws',
                config('services.ws.host') . (config('services.ws.port') ? ':' . config('services.ws.port') : '')
            );
        }
    }

    private function registerPublicData()
    {
        $this->app['request']->attributes->add([
            'public'        => route('home'),
            'cdn'           => config('app.cdn') ? ('//' . config('app.cdn')) : route('home'),
            'ping'          => route('ping', [], false),
            'ping_interval' => config('session.lifetime') - 5 // every 10 minutes
        ]);
    }
}
