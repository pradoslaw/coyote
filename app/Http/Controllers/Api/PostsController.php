<?php

namespace Coyote\Http\Controllers\Api;

use Coyote\Http\Resources\PostResource;
use Coyote\Post;
use Coyote\Repositories\Contracts\PostRepositoryInterface as PostRepository;
use Coyote\Repositories\Criteria\EagerLoading;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Coyote\Repositories\Criteria\Sort;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Auth\Factory as Auth;

class PostsController extends Controller
{
    use AuthorizesRequests;

    /**
     * @var \Coyote\User
     */
    private $user;

    /**
     * @var PostRepository
     */
    private $post;

    /**
     * @param Auth $auth
     * @param PostRepository $post
     */
    public function __construct(Auth $auth, PostRepository $post)
    {
        $this->user = $auth->guard('api')->user();
        $this->post = $post;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $validator = validator($request->all(), [
            'order'         => 'nullable|in:asc,desc'
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 422);
        }

        $this->post->pushCriteria(new Sort('id', $request->input('order', Sort::DESC)));

        $this->post->pushCriteria(new EagerLoading($this->includeUser()));
        $this->post->pushCriteria(new OnlyThoseWithAccess($this->user));

        $paginate = $this->post->paginate();

        return PostResource::collection($paginate);
    }

    /**
     * @param Post $post
     * @return PostResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Post $post)
    {
        $this->authorizeForUser($this->user, 'access', $post->forum);

        $post->load($this->includeUser());

        $post->load(['comments' => function ($builder) {
            return $builder->with(['user' => function ($query) {
                return $query->select(['id', 'name', 'photo', 'deleted_at', 'is_blocked'])->withTrashed();
            }]);
        }]);

        PostResource::withoutWrapping();

        return new PostResource($post);
    }

    private function includeUser(): array
    {
        return ['user' => function ($builder) {
            return $builder->select(['id', 'name', 'photo', 'deleted_at', 'is_blocked'])->withTrashed();
        }];
    }
}
