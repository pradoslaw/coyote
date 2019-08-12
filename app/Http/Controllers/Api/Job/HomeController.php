<?php

namespace Coyote\Http\Controllers\Api\Job;

use Coyote\Http\Resources\JobResource;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Repositories\Criteria\EagerLoading;
use Coyote\Repositories\Criteria\EagerLoadingWithCount;
use Coyote\Repositories\Criteria\Job\PriorDeadline;
use Coyote\Repositories\Criteria\Sort;
use Illuminate\Routing\Controller;

class HomeController extends Controller
{
    /**
     * @param JobRepository $job
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(JobRepository $job)
    {
        $eagerCriteria = new EagerLoading(['firm:id,name,slug,logo', 'locations', 'tags', 'currency']);

        $job->pushCriteria($eagerCriteria);
        $job->pushCriteria(new EagerLoadingWithCount(['comments']));
        $job->pushCriteria(new PriorDeadline());
        $job->pushCriteria(new Sort('jobs.id', Sort::DESC));

        $job->applyCriteria();

        $data = $job->paginate();

        return JobResource::collection($data);
    }
}
