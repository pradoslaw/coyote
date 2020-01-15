<?php

namespace Coyote\Http\Controllers\Api;

use Coyote\Forum;
use Coyote\Http\Resources\ForumCollection;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Repositories\Criteria\Forum\AccordingToUserOrder;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Coyote\Services\Forum\Tracker;
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

        $result = $forum
            ->categories($guestId)
            ->map(function (Forum $forum) use ($guestId) {
                $post = $forum->post;

                if ($post) {
                    $post->topic->setRelation('forum', $forum);
                    $post->setRelation('topic', Tracker::make($post->topic, $guestId));
                }

                return $forum;
            });

        return new ForumCollection($result);
    }
}
