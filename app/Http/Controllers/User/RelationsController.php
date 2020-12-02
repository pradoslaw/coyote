<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Illuminate\Contracts\Cache\Repository as Cache;

class RelationsController extends Controller
{
    public function block(int $relatedUserId, Cache $cache)
    {
        abort(500, $relatedUserId === $this->userId);

        $this->auth->relations()->updateOrInsert(['related_user_id' => $relatedUserId, 'is_blocked' => true, 'user_id' => $this->userId]);

        $cache->forget('followers:' . $this->userId);
    }
}
