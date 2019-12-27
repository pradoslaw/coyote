<?php

namespace Coyote\Http\Controllers\Api;

use Coyote\Http\Resources\PostResource;
use Coyote\Repositories\Contracts\PostRepositoryInterface as PostRepository;
use Coyote\Repositories\Criteria\EagerLoading;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Coyote\Repositories\Criteria\Sort;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Auth\Factory as Auth;

class PostsController extends Controller
{
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

        $this->includeUser();
        $this->post->pushCriteria(new OnlyThoseWithAccess($this->user));

        $paginate = $this->post->paginate();

        return PostResource::collection($paginate);
    }

    /**
     * @param Gate $gate
     * @param int $id
     * @return PostResource|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function show(Gate $gate, int $id)
    {
        $this->includeUser();

        $this->post->pushCriteria(new EagerLoading(['comments' => function ($builder) {
            return $builder->with(['user' => function ($query) {
                return $query->select(['id', 'name', 'photo'])->withTrashed();
            }]);
        }]));

        $post = $this->post->findOrFail($id);

        if ($gate->forUser($this->user)->denies('access', $post->forum)) {
            return response('Unauthorized.', 401);
        }

        return new PostResource($post);
    }

    private function includeUser()
    {
        $this->post->pushCriteria(new EagerLoading(['user' => function ($builder) {
            return $builder->select(['id', 'name', 'photo'])->withTrashed();
        }]));
    }
}
