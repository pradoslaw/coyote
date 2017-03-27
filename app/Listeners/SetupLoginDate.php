<?php

namespace Coyote\Listeners;

use Carbon\Carbon;
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
        $event->user->visits += 1;
        $event->user->visited_at = Carbon::now();

        $event->user->save();
    }
}
