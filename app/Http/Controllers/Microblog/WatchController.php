<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;
use Coyote\Microblog;
use Coyote\Microblog\Watch;

/**
 * Class WatchController
 * @package Coyote\Http\Controllers\Microblog
 */
class WatchController extends Controller
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

        $watch = Watch::where('microblog_id', $id)->where('user_id', auth()->user()->id)->first();

        if ($watch) {
            $watch->delete();
        } else {
            Watch::create(['microblog_id' => $id, 'user_id' => auth()->user()->id]);
        }
    }
}
