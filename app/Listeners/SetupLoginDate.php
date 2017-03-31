<?php

namespace Coyote\Listeners;

use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class SetupLoginDate // <!-- do not put ShouldQueue
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the login event (either via oauth or via regular form or session create).
     *
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        // don't update updated_at column
        $event->user->timestamps = false;

        $event->user->forceFill([
            'ip'            => $this->request->ip(),
            'is_online'     => true,
            'visited_at'    => Carbon::now()
        ]);

        $event->user->save();
    }
}
