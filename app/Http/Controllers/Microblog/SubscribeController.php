<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;

/**
 * Class SubscribeController
 * @package Coyote\Http\Controllers\Microblog
 */
class SubscribeController extends Controller
{
    /**
     * Mozliwosc obserwowania danego wpisu na mikroblogu
     *
     * @param \Coyote\Microblog $microblog
     * @return \Illuminate\Http\JsonResponse
     */
    public function post($microblog)
    {
        if (auth()->guest()) {
            return response()->json(['error' => 'Musisz być zalogowany, aby móc obserwować ten wpis.'], 500);
        }

        $subscriber = $microblog->subscribers()->forUser($this->userId)->first();

        if ($subscriber) {
            $subscriber->delete();
        } else {
            $microblog->subscribers()->create(['user_id' => $this->userId]);
        }
    }
}
