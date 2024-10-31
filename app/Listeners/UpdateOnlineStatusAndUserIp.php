<?php
namespace Coyote\Listeners;

use Carbon\Carbon;
use Coyote\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class UpdateOnlineStatusAndUserIp // <!-- do not put ShouldQueue
{
    public function __construct(private Request $request)
    {
    }

    public function handle(Login $event): void
    {
        $this->update($event->user);
    }

    private function update(User $user): void
    {
        $user->timestamps = false;
        $user->forceFill([
            'ip'         => $this->request->ip(),
            'is_online'  => true,
            'visited_at' => Carbon::now(),
        ]);
        $user->save();
    }
}
