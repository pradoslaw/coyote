<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;

class RelationsController extends Controller
{
    public function block(int $relatedUserId)
    {
        $this->auth->relations()->updateOrInsert(['related_user_id' => $relatedUserId, 'is_blocked' => true, 'user_id' => $this->userId]);
    }
}
