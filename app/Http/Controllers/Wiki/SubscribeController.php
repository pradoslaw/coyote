<?php

namespace Coyote\Http\Controllers\Wiki;

use Coyote\Http\Controllers\Controller;

class SubscribeController extends Controller
{
    /**
     * @param \Coyote\Wiki $wiki
     */
    public function index($wiki)
    {
        $subscribe = $wiki->subscribers()->forUser($this->userId)->first();

        if (!$subscribe) {
            $wiki->subscribers()->create(['user_id' => $this->userId]);
        } else {
            $subscribe->delete();
        }
    }
}
