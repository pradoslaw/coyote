<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;
use Illuminate\Auth\AuthenticationException;

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
     * @throws AuthenticationException
     */
    public function post($microblog)
    {
        if (auth()->guest()) {
            throw new AuthenticationException('Musisz być zalogowany, aby móc obserwować ten wpis.');
        }

        $subscriber = $microblog->subscribers()->forUser($this->userId)->first();

        if ($subscriber) {
            $subscriber->delete();
        } else {
            $microblog->subscribers()->create(['user_id' => $this->userId]);
        }
    }
}
