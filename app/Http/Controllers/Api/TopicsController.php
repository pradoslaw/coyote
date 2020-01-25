<?php

namespace Coyote\Http\Controllers\Api;

use Coyote\Http\Resources\TagResource;
use Coyote\Http\Resources\TopicResource;
use Coyote\Repositories\Contracts\TopicRepositoryInterface;
use Coyote\Repositories\Criteria\EagerLoading;
use Coyote\Repositories\Criteria\Sort;
use Coyote\Repositories\Criteria\Topic\LoadMarkTime;
use Coyote\Repositories\Criteria\Topic\OnlyThoseWithAccess;
use Coyote\Services\Forum\Tracker;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
     * @var string|null
     */
    private $guestId;

    /**
     * @param Auth $auth
     */
    public function __construct(Auth $auth)
    {
        TagResource::$url = function ($name) {
            return route('forum.tag', [urlencode($name)]);
        };

        $this->user = $auth->guard('api')->user();
        $this->guestId = $this->user->guest_id ?? null;
    }

    /**
     * @param TopicRepositoryInterface $topic
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
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

        $topic->pushCriteria(new Sort($request->input('sort', 'id'), $request->input('order', Sort::DESC)));
        $topic->pushCriteria(new EagerLoading(['tags']));
        $topic->pushCriteria(new LoadMarkTime($this->guestId));
        $topic->pushCriteria(new EagerLoading(['forum' => function (BelongsTo $builder) {
            return $builder->select('forums.id', 'forums.name', 'forums.slug', 'is_prohibited')->withForumMarkTime($this->guestId);
        }]));

        $topic->pushCriteria(new OnlyThoseWithAccess($this->user));

        /** @var \Illuminate\Pagination\LengthAwarePaginator $paginate */
        $paginate = $topic->paginate();

        $paginate->setCollection(
            $paginate
                ->getCollection()
                ->map(function ($model) {
                    return Tracker::make($model, $this->guestId);
                })
        );

        return TopicResource::collection($paginate);
    }

    /**
     * @param Topic $topic
     * @return TopicResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Topic $topic)
    {
        $topic
            ->load(['tags'])
            ->load(['forum' => function ($builder) {
                return $builder->select('forums.id', 'forums.name', 'forums.slug', 'is_prohibited')->withForumMarkTime($this->guestId);
            }]);

        $this->authorize('access', $topic->forum);
        $topic->markTime($this->guestId);

        TopicResource::withoutWrapping();

        return new TopicResource(Tracker::make($topic, $this->guestId));
    }
}
