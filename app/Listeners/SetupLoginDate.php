<?php

namespace Coyote\Listeners;

use Illuminate\Auth\Events\Login;

class SetupLoginDate
{
    /**
     * Handle the event.
     *
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        $event->user->is_online = true;
        $event->user->save();
    }
}
