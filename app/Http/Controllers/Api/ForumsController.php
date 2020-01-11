<?php

namespace Coyote\Http\Controllers\Api;

use Coyote\Http\Resources\ForumCollection;
use Coyote\Http\Resources\ForumResource;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Auth\Factory as Auth;

class ForumsController extends Controller
{
    public function index(ForumRepository $forum, Auth $auth)
    {
        $user = $auth->guard('api')->user();
        $guestId = $user->guest_id ?? null;

        debugbar()->startMeasure('foo');

        $result = $forum->categories($guestId);

        debugbar()->stopMeasure('foo');

        return new ForumCollection($result);
    }
}
