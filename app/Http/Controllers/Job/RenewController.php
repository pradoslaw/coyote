<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Job;
use Coyote\Repositories\Contracts\FirmRepositoryInterface as FirmRepository;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Repositories\Contracts\PlanRepositoryInterface as PlanRepository;

class RenewController extends Controller
{
    /**
     * @var JobRepository
     */
    private $job;

    /**
     * @var FirmRepository
     */
    private $firm;

    /**
     * @var PlanRepository
     */
    private $plan;

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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index(Job $job)
    {
        abort_unless($job->is_expired, 404);

        // load default firm regardless of offer is private or not
        if (!$job->firm_id) {
            $firm = $this->firm->loadDefaultFirm($this->userId);
            $firm->is_private = $job->exists && !$job->firm_id;

            $job->firm()->associate($firm);
        }

        $job->load(['tags', 'features', 'locations', 'country']);
        $job->firm->load('benefits');

        $job->setDefaultUserId($this->userId);
        $job->setDefaultFeatures($this->job->getDefaultFeatures($this->userId));
        $job->setDefaultPlanId($this->plan->getDefaultId());

        // reset all plan values
        $job->is_boost = $job->is_publish = $job->is_ads = $job->is_on_top = $job->is_highlight = false;

        $this->request->session()->put(Job::class, $job);

        return redirect()->route('job.submit');
    }
}
