<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Resources\JobResource;
use Coyote\Repositories\Criteria\EagerLoading;
use Coyote\Repositories\Criteria\EagerLoadingWithCount;
use Coyote\Repositories\Criteria\Job\IncludeSubscribers;

class MineController extends BaseController
{
    public function index()
    {
        $eagerCriteria = new EagerLoading(['firm', 'locations', 'tags', 'currency']);

        $this->job->pushCriteria($eagerCriteria);
        $this->job->pushCriteria(new EagerLoadingWithCount(['comments']));
        $this->job->pushCriteria(new IncludeSubscribers($this->userId));

        $paginator = $this->job->published($this->userId);

        return $this->view('job.mine', [
            'jobs' => JobResource::collection($paginator)->toResponse($this->request)->getData(true),
            'subscribed' => JobResource::collection($this->job->subscribes($this->userId))->toArray($this->request)
        ]);
    }
}
