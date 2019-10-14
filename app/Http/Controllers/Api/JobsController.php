<?php

namespace Coyote\Http\Controllers\Api\Job;

use Illuminate\Http\Resources\Json\Resource;
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

class JobsController extends Controller
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
     * @return JobResource
     */
    public function show(Job $job)
    {
        Resource::withoutWrapping();

        $this->job->pushCriteria(new EagerLoading(['firm:id,name,slug,logo', 'locations', 'tags', 'currency']));
        $this->job->pushCriteria(new EagerLoading('features'));
        $this->job->pushCriteria(new EagerLoadingWithCount(['comments']));

        return new JobResource($job);
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
        $job->firm()->dissociate();

        $this->authorizeForUser($user, 'update', $job);

        $job->fill($request->all());

        if ($request->has('firm.name')) {
            $firm = $this->firm->loadFirm($user->id, $request->input('firm.name'));

            $firm->fill($request->input('firm'));
            $job->firm()->associate($firm);
        }

        $this->saveInTransaction($job, $user);

        return response(new JobResource($job), $job->wasRecentlyCreated ? 201 : 200);
    }
}
