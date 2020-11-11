<?php

namespace Coyote\Services\Job;

use Coyote\Feature;
use Coyote\Job;
use Coyote\Payment;
use Coyote\Repositories\Contracts\FirmRepositoryInterface as FirmRepository;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Repositories\Contracts\PlanRepositoryInterface as PlanRepository;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Objects\Job as Stream_Job;
use Coyote\User;

trait SubmitsJob
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
        $this->job = $job;
        $this->firm = $firm;
        $this->plan = $plan;
    }

    /**
     * @param Job $job
     * @param User $user
     * @return Job
     */
    public function loadDefaults(Job $job, User $user): Job
    {
        $firm = $this->firm->loadDefaultFirm($user->id);
        $job->firm()->associate($firm);

        $job->firm->load(['benefits', 'gallery']);

        $job->plan_id = request('default_plan') ?? $this->plan->findDefault()->id;
        $job->email = $user->email;
        $job->setRelation('features', $this->getDefaultFeatures($job));

        if (!count($job->locations)) {
            $job->locations->add(new Job\Location());
        }

        return $job;
    }

    protected function getDefaultFeatures(Job $job)
    {
        $features = $this->job->getDefaultFeatures($this->userId);
        $models = [];

        foreach ($features as $feature) {
            $checked = (int) $feature['checked'];

            $pivot = $job->features()->newPivot([
                'checked'       => $checked,
                'value'         => $checked ? ($feature['value'] ?? null) : null
            ]);

            $models[] = Feature::findOrNew($feature['id'])->setRelation('pivot', $pivot);
        }

        return $models;
    }

    /**
     * @param Job $job
     * @return Payment|null
     */
    protected function getUnpaidPayment(Job $job): ?Payment
    {
        return !$job->is_publish ? $job->getUnpaidPayment() : null;
    }
}
