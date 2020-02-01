<?php

namespace Coyote\Http\Controllers\Api;

use Coyote\Http\Resources\ForumCollection;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Repositories\Criteria\Forum\AccordingToUserOrder;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Coyote\Services\Guest;
use Illuminate\Container\Container;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Auth\Factory as Auth;

class ForumsController extends Controller
{
    /**
     * @var \Coyote\User
     */
    private $user;

    /**
     * @var string|null
     */
    private $guestId;

    /**
     * @param Container $app
     * @param Auth $auth
     */
    public function __construct(Container $app, Auth $auth)
    {
        $this->user = $auth->guard('api')->user();
        $this->guestId = $this->user->guest_id ?? null;

        $app->singleton(Guest::class, function () {
            return new Guest($this->guestId);
        });
    }

    /**
     * @param ForumRepository $forum
     * @return ForumCollection
     */
    public function index(ForumRepository $forum)
    {
        ForumCollection::withoutWrapping();

        $forum->pushCriteria(new OnlyThoseWithAccess($this->user));
        $forum->pushCriteria(new AccordingToUserOrder($user->id ?? null));

        $result = $forum
            ->categories($this->guestId)
            ->mapCategory();

        return new ForumCollection($result);
    }
}
