<?php

namespace Coyote\Http\Controllers\Api;

use Coyote\Http\Resources\ForumCollection;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Repositories\Criteria\Forum\AccordingToUserOrder;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Auth\Factory as Auth;

class ForumsController extends Controller
{
    public function index(ForumRepository $forum, Auth $auth)
    {
        ForumCollection::withoutWrapping();

        $user = $auth->guard('api')->user();
        $guestId = $user->guest_id ?? null;

        $forum->pushCriteria(new OnlyThoseWithAccess($user));
        $forum->pushCriteria(new AccordingToUserOrder($user->id ?? null));

        debugbar()->startMeasure('foo');

        $result = $forum->categories($guestId);
        $data = new ForumCollection($result);

        debugbar()->stopMeasure('foo');

        return $data;
    }
}
