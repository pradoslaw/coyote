<?php

namespace Coyote\Plugins\Password;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Coyote\User;

class ServiceProvider extends EventServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);

        // W starej wersji 4programmers.net hasla byly hashowane przy pomocy sha256 + sol. Jezeli w bazie
        // danych jest stary hash, to zmieniamy hasha i zapisujemy do bazy danych
        $events->listen('auth.attempt', function ($credentials) {
            $user = User::where('name', $credentials['name'])->first();

            if ($user && $user->salt && $user->password === hash('sha256', $user->salt . $credentials['password'])) {
                $user->password = bcrypt($credentials['password']);
                $user->salt = null;
                $user->save();
            }
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
