<?php

namespace Coyote\Http\Controllers\Api;

use Coyote\Http\Resources\TopicResource;
use Coyote\Repositories\Contracts\TopicRepositoryInterface;
use Coyote\Repositories\Criteria\EagerLoading;
use Coyote\Repositories\Criteria\Sort;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Auth\Factory as Auth;

class TopicsController extends Controller
{
    /**
     * @param TopicRepositoryInterface $topic
     * @param Auth $auth
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(TopicRepositoryInterface $topic, Auth $auth, Request $request)
    {
        $user = $auth->guard('api')->user();
        $guestId = $user->guest_id ?? null;

        $postRelation = function ($builder) {
            return $builder
                ->select('id', 'created_at', 'user_name', 'user_id')
                ->with(['user' => function ($query) {
                    return $query->select(['id', 'name', 'photo'])->withTrashed();
                }]);
        };

        $topic->pushCriteria(new Sort($request->input('sort', 'id'), Sort::DESC));

        $topic->pushCriteria(new EagerLoading(['tags']));
        $topic->pushCriteria(new EagerLoading(['firstPost' => $postRelation, 'lastPost' => $postRelation]));

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

        $paginate = $topic->paginate();

        return TopicResource::collection($paginate);
    }
}
