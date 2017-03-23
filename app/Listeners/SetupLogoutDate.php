<?php

namespace Coyote\Listeners;

use Carbon\Carbon;
use Illuminate\Auth\Events\Logout;

class SetupLogoutDate
{
    /**
     * Handle the event.
     *
     * @param  Logout  $event
     * @return void
     */
    public function handle(Logout $event)
    {
        $event->user->visited_at = Carbon::now();
        $event->user->visits = $event->user->visits + 1;
        $event->user->is_online = false;

        $event->user->save();
    }
}
