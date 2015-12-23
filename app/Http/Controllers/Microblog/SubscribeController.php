<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;
use Coyote\Microblog;
use Coyote\Microblog\Subscriber;

/**
 * Class SubscribeController
 * @package Coyote\Http\Controllers\Microblog
 */
class SubscribeController extends Controller
{
    /**
     * Mozliwosc obserwowania danego wpisu na mikroblogu
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function post($id)
    {
        if (auth()->guest()) {
            return response()->json(['error' => 'Musisz być zalogowany, aby móc obserwować ten wpis.'], 500);
        }

        $subscriber = Subscriber::where('microblog_id', $id)->where('user_id', auth()->user()->id)->first();

        if ($subscriber) {
            $subscriber->delete();
        } else {
            Subscriber::create(['microblog_id' => $id, 'user_id' => auth()->user()->id]);
        }
    }
}
