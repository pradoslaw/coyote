<?php

namespace Coyote\Plugins\Password;

use Illuminate\Auth\Events\Attempting;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Coyote\User;

class ServiceProvider extends EventServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        // W starej wersji 4programmers.net hasla byly hashowane przy pomocy sha256 + sol. Jezeli w bazie
        // danych jest stary hash, to zmieniamy hasha i zapisujemy do bazy danych
        $this->app['events']->listen(Attempting::class, function (Attempting $attempting) {
            if (empty($attempting->credentials['name'])) {
                return;
            }

            $user = User::where('name', $attempting->credentials['name'])->first();

            if ($user && $user->salt
                && $user->password === hash('sha256', $user->salt . $attempting->credentials['password'])) {
                $user->password = bcrypt($attempting->credentials['password']);
                $user->salt = null;
                $user->save();
            }
        });
    }
}
