<?php
namespace Coyote\Providers\Neon;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider;

class ServiceProvider extends RouteServiceProvider
{
    public function loadRoutes(): void
    {
        $this->get('/events', [
            'uses' => fn() => redirect('https://wydarzenia.4programmers.net/'),
        ]);
    }
}
