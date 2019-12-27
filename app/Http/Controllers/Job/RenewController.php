<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Job;
use Coyote\Services\Job\Draft;
use Coyote\Services\Job\SubmitsJob;
use Coyote\Repositories\Contracts\FirmRepositoryInterface as FirmRepository;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Repositories\Contracts\PlanRepositoryInterface as PlanRepository;

class RenewController extends Controller
{
    use SubmitsJob;

    /**
     * @param JobRepository $job
     * @param FirmRepository $firm
     * @param PlanRepository $plan
     */
    public function __construct(JobRepository $job, FirmRepository $firm, PlanRepository $plan)
    {
        parent::__construct();

        $this->job = $job;
        $this->firm = $firm;
        $this->plan = $plan;
    }

    /**
     * @param Job $job
     * @param Draft $draft
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Job $job, Draft $draft)
    {
        abort_unless($job->is_expired, 404);

        $job = $this->loadDefaults($job, $this->auth);

        unset($job->id);
        $job->exists = false; // new job offer

        $job->user_id = $this->userId;

        // reset all plan values
        $job->is_boost = $job->is_publish = $job->is_ads = $job->is_on_top = $job->is_highlight = false;
        // reset views counter
        $job->views = 1;

        $this->authorize('update', $job);
        $this->authorize('update', $job->firm);

        $draft->put(Job::class, $job);

        return redirect()->route('job.submit');
    }
}
