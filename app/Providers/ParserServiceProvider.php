<?php

namespace Coyote\Providers;

use Illuminate\Support\ServiceProvider;
use Coyote\Parser\Scenarios\Microblog as Parser_Microblog;

class ParserServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Parser\Microblog', function ($app) {
            return new Parser_Microblog(
                $app['Coyote\Repositories\Eloquent\UserRepository'],
                $app['Coyote\Repositories\Eloquent\WordRepository']
            );
        });
    }
}
