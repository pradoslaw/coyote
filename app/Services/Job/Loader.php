<?php

namespace Coyote\Services\Job;

use Coyote\Job;
use Coyote\Repositories\Contracts\FirmRepositoryInterface as FirmRepository;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Repositories\Contracts\PlanRepositoryInterface as PlanRepository;
use Illuminate\Contracts\Auth\Guard;

class Loader
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
     * @var Guard
     */
    private $auth;

    /**
     * @param JobRepository $job
     * @param FirmRepository $firm
     * @param PlanRepository $plan
     * @param Guard $auth
     */
    public function __construct(JobRepository $job, FirmRepository $firm, PlanRepository $plan, Guard $auth)
    {
        $this->job = $job;
        $this->firm = $firm;
        $this->plan = $plan;
        $this->auth = $auth;
    }

    /**
     * @param Job $job
     * @return Job
     */
    public function init(Job $job): Job
    {
        // load default firm regardless of offer is private or not
        if (!$job->firm_id) {
            $firm = $this->firm->loadDefaultFirm($this->auth->id());
            $firm->is_private = $job->exists && !$job->firm_id;

            $job->firm()->associate($firm);
        }

        $job->load(['tags', 'features', 'locations', 'country']);
        $job->firm->load('benefits');

        $job->setDefaultUserId($this->auth->id());
        $job->setDefaultFeatures($this->job->getDefaultFeatures($this->auth->id()));
        $job->setDefaultPlanId($this->plan->getDefaultId());

        return $job;
    }
}
