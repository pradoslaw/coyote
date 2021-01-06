<?php

namespace Coyote\Http\Controllers\Api;

use Coyote\Http\Resources\TagResource;
use Coyote\Http\Resources\Api\TopicResource;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as TopicRepository;
use Coyote\Repositories\Criteria\EagerLoading;
use Coyote\Repositories\Criteria\Sort;
use Coyote\Repositories\Criteria\Topic\LoadMarkTime;
use Coyote\Repositories\Criteria\Topic\OnlyThoseWithAccess;
use Coyote\Services\Forum\Tracker;
use Coyote\Services\Guest;
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
     * @var TopicRepository
     */
    private $repository;

    /**
     * @var string|null
     */
    private $guestId;

    /**
     * @param Auth $auth
     */
    public function __construct(Auth $auth, TopicRepository $repository)
    {
        TagResource::urlResolver(fn ($name) => route('forum.tag', [urlencode($name)]));

        $this->repository = $repository;
        $this->user = $auth->guard('api')->user();
        $this->guestId = $this->user->guest_id ?? null;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $validator = validator($request->all(), [
            'sort'          => 'nullable|in:id,last_post_id',
            'order'         => 'nullable|in:asc,desc'
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 422);
        }

        $this->repository->pushCriteria(new Sort($request->input('sort', 'id'), $request->input('order', Sort::DESC)));
        $this->repository->pushCriteria(new EagerLoading(['tags']));
        $this->repository->pushCriteria(new LoadMarkTime($this->guestId));
        $this->repository->pushCriteria(new EagerLoading(['forum' => function (BelongsTo $builder) {
            return $builder->select('forums.id', 'forums.name', 'forums.slug', 'is_prohibited')->withForumMarkTime($this->guestId);
        }]));

        $this->repository->pushCriteria(new OnlyThoseWithAccess($this->user));

        /** @var \Illuminate\Pagination\LengthAwarePaginator $paginate */
        $paginate = $this->repository->paginate();

        $guest = new Guest($this->guestId);
        $repository = $this->repository;

        $paginate->setCollection(
            $paginate
                ->getCollection()
                ->map(function ($model) use ($guest, $repository) {
                    return (new Tracker($model, $guest))->setRepository($repository);
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

        $this->authorizeForUser($this->user, 'access', $topic->forum);
        $topic->markTime($this->guestId);

        $guest = new Guest($this->guestId);

        TopicResource::withoutWrapping();

        return new TopicResource((new Tracker($topic, $guest))->setRepository($this->repository));
    }
}
