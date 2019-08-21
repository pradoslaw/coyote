<?php

namespace Coyote\Http\Controllers\Api\Job;

use Coyote\Http\Resources\JobResource;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Repositories\Criteria\EagerLoading;
use Coyote\Repositories\Criteria\EagerLoadingWithCount;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Routing\Controller;

class OfferController extends Controller
{
    /**
     * @param int $id
     * @param JobRepository $job
     * @return JobResource
     */
    public function index(int $id, JobRepository $job)
    {
        Resource::withoutWrapping();

        $job->pushCriteria(new EagerLoading(['firm:id,name,slug,logo', 'locations', 'tags', 'currency']));
        $job->pushCriteria(new EagerLoading('features'));
        $job->pushCriteria(new EagerLoadingWithCount(['comments']));

        return new JobResource($job->find($id));
    }
}
