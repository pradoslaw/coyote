<?php

namespace Coyote\Http\Controllers\Api;

use Coyote\Http\Resources\TagResource;
use Coyote\Http\Resources\TopicResource;
use Coyote\Repositories\Contracts\TopicRepositoryInterface;
use Coyote\Repositories\Criteria\EagerLoading;
use Coyote\Repositories\Criteria\Sort;
use Coyote\Repositories\Criteria\Topic\OnlyThoseWithAccess;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Auth\Factory as Auth;

class TopicsController extends Controller
{
    use AuthorizesRequests;

    /**
     * @var User
     */
    private $user;

    /**
     * TopicsController constructor.
     * @param Auth $auth
     */
    public function __construct(Auth $auth)
    {
        $this->user = $auth->guard('api')->user();

        TagResource::$url = function ($name) {
            return route('forum.tag', [urlencode($name)]);
        };
    }

    /**
     * @param TopicRepositoryInterface $topic
     * @param Auth $auth
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(TopicRepositoryInterface $topic, Request $request)
    {
        $validator = validator($request->all(), [
            'sort'          => 'nullable|in:id,last_post_id',
            'order'         => 'nullable|in:asc,desc'
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 422);
        }

        $guestId = $this->user->guest_id ?? null;

        $topic->pushCriteria(new Sort($request->input('sort', 'id'), $request->input('order', Sort::DESC)));
        $topic->pushCriteria(new EagerLoading(['tags']));

        if ($guestId) {
            $topic->pushCriteria(new EagerLoading(['tracks' => function ($builder) use ($guestId) {
                return $builder->where('guest_id', '=', $guestId);
            }]));
        }

        $topic->pushCriteria(new EagerLoading(['forum' => function ($builder) use ($guestId) {
            $builder->select('id', 'name', 'slug');

            if ($guestId) {
                $builder->with(['tracks' => function ($query) use ($guestId) {
                    return $query->where('guest_id', '=', $guestId);
                }]);
            }

            return $builder;
        }]));

        $topic->pushCriteria(new OnlyThoseWithAccess($this->user));

        $paginate = $topic->paginate();

        return TopicResource::collection($paginate);
    }

    /**
     * @param Topic $topic
     * @return TopicResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Topic $topic)
    {
        $this->authorizeForUser($this->user, 'access', $topic->forum);

        $topic->load(['tags']);

        TopicResource::withoutWrapping();

        return new TopicResource($topic);
    }
}
