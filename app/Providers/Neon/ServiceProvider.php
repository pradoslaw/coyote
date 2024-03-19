<?php
namespace Coyote\Providers\Neon;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Neon\View;

class ServiceProvider extends RouteServiceProvider
{
    public function loadRoutes(): void
    {
        $this->get('/events', [
            'uses' => function () {
                $view = new View('');
                return $view->html();
            },
        ]);
    }
}
