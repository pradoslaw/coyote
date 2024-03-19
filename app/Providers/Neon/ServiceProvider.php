<?php
namespace Coyote\Providers\Neon;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Neon\View;

class ServiceProvider extends RouteServiceProvider
{
    public function register(): void
    {
        parent::register();
        $this->app->instance(View::class, new View(''));
    }

    public function loadRoutes(): void
    {
        $this->get('/events', [
            'uses' => function () {
                $view = $this->app->get(View::class);
                return $view->html();
            },
        ]);
    }
}
