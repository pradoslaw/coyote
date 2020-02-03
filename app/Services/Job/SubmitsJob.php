<?php

namespace Coyote\Services\Job;

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
        // load default firm regardless of offer is private or not
        if (!$job->firm_id) {
            $firm = $this->firm->loadDefaultFirm($user->id);
            $firm->is_private = $job->exists && !$job->firm_id;

            $job->firm()->associate($firm);
        }

        $job->load(['tags', 'features', 'locations.country']);
        $job->firm->load(['benefits', 'gallery', 'industries']);

        if (!$job->exists) {
            $job->user_id = $user->id;
            $job->plan_id = request('default_plan') ?? $this->plan->findDefault()->id;
            $job->email = $user->email;
            $job->setAttribute('features', $this->job->getDefaultFeatures($user->id));
        }

        if (!count($job->locations)) {
            $job->locations->add(new Job\Location());
        }

        return $job;
    }

    /**
     * @param Job $job
     * @param User $user
     * @return Job
     */
    protected function prepareAndSave(Job $job, User $user)
    {
        $tags = [];
        if (count($job->tags)) {
            $order = 0;

            foreach ($job->tags as $tag) {
                $model = $tag->firstOrCreate(['name' => $tag->name]);

                $tags[$model->id] = [
                    'priority'  => $tag->pivot->priority ?? 0,
                    'order'     => ++$order
                ];
            }
        }

        $features = [];
        foreach ($job->features as $feature) {
            $features[$feature->id] = $feature->pivot->toArray();
        }

        $activity = $job->id ? Stream_Update::class : Stream_Create::class;

        if (!$job->firm || $job->firm->is_private) {
            $job->firm()->dissociate();
        } elseif ($job->firm->name) { // firm name is required to save firm
            // user might click on "add new firm" button in form. make sure user_id is set up.
            $job->firm->setDefaultUserId($job->user_id);

            $this->authorizeForUser($user, 'update', $job->firm);

            // fist, we need to save firm because firm might not exist.
            $job->firm->save();

            // reassociate job with firm. user could change firm, that's why we have to do it again.
            $job->firm()->associate($job->firm);
            // remove old benefits and save new ones.
            $job->firm->benefits()->push($job->firm->benefits);
            // sync industries
            $job->firm->industries()->sync($job->firm->industries);
            $job->firm->gallery()->push($job->firm->gallery);
        }

        $job->save();
        $job->locations()->push($job->locations);

        $job->tags()->sync($tags);
        $job->features()->sync($features);

        stream($activity, (new Stream_Job)->map($job));

        return $job;
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
