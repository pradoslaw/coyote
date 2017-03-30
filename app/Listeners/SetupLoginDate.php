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
     * Handle the login event (either via oauth or via regular form)
     *
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        $event->user->forceFill([
            'ip'            => $this->request->ip(),
            'is_online'     => true,
            'visited_at'    => Carbon::now()
        ]);

        $event->user->save();
    }
}
