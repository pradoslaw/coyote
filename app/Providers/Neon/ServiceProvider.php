<?php
namespace Coyote\Providers\Neon;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Neon\Application;

class ServiceProvider extends RouteServiceProvider
{
    public function register(): void
    {
        parent::register();
        $this->app->instance(
            Application::class,
            new Application('4programmers.net'));
    }

    public function loadRoutes(): void
    {
        $this->get('/events', [
            'uses' => function () {
                /** @var Application $application */
                $application = $this->app->get(Application::class);
                return $application->html();
            },
        ]);
    }
}
