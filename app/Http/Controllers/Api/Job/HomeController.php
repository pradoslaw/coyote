<?php

namespace Coyote\Http\Controllers\Api\Job;

use Coyote\Http\Requests\Job\ApiRequest;
use Coyote\Http\Resources\JobResource;
use Coyote\Job;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Repositories\Criteria\EagerLoading;
use Coyote\Repositories\Criteria\EagerLoadingWithCount;
use Coyote\Repositories\Criteria\Job\OnlyPublished;
use Coyote\Repositories\Criteria\Job\PriorDeadline;
use Coyote\Repositories\Criteria\Sort;
use Coyote\Services\Job\SubmitsJob;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Auth\Factory as Auth;

class HomeController extends Controller
{
    use AuthorizesRequests, SubmitsJob;

    /**
     * @param JobRepository $job
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $this->job->pushCriteria(new EagerLoading(['firm:id,name,slug,logo', 'locations', 'tags', 'currency']));
        $this->job->pushCriteria(new EagerLoadingWithCount(['comments']));
        $this->job->pushCriteria(new PriorDeadline());
        $this->job->pushCriteria(new OnlyPublished());
        $this->job->pushCriteria(new Sort('jobs.id', Sort::DESC));

        $this->job->applyCriteria();

        $data = $this->job->paginate();

        return JobResource::collection($data);
    }

    /**
     * @param Job $job
     * @param ApiRequest $request
     * @param Auth $auth
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function save(Job $job, ApiRequest $request, Auth $auth)
    {
        $user = $auth->guard('api')->user();
        $job = $this->loadDefaults($job, $user);

        $this->authorizeForUser($user, 'update', $job);

        $job->fill($request->all());
        $this->saveInTransaction($job, $user);

        return response(new JobResource($job), $job->wasRecentlyCreated ? 201 : 200);
    }
}
