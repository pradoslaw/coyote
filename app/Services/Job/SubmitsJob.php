<?php
namespace Coyote\Services\Job;

use Coyote\Feature;
use Coyote\Job;
use Coyote\Payment;
use Coyote\Repositories\Eloquent\FirmRepository;
use Coyote\Repositories\Eloquent\JobRepository;
use Coyote\Repositories\Eloquent\PlanRepository;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Objects\Job as Stream_Job;
use Coyote\Tag;
use Coyote\User;
use Illuminate\Http\Request;

trait SubmitsJob
{
    public function __construct(
        private JobRepository  $job,
        private FirmRepository $firm,
        private PlanRepository $plan) {}

    public function loadDefaults(Job $job, User $user): Job
    {
        $firm = $this->firm->loadDefaultFirm($user->id);
        $job->firm()->associate($firm);
        $job->firm->load(['benefits', 'assets']);
        $job->plan_id = request('plan');
        $job->email = $user->email;
        $job->user_id = $user->id;
        $job->setRelation('features', $this->getDefaultFeatures($job, $user));
        return $job;
    }

    protected function saveRelations(Job $job, User $user): Job
    {
        $activity = $job->id ? Stream_Update::class : Stream_Create::class;
        if ($job->firm) {
            if (!$job->firm->exists) {
                $job->firm->save();
            }
            // reassociate job with firm. user could change firm, that's why we have to do it again.
            $job->firm()->associate($job->firm);
            // remove old benefits and save new ones.
            $job->firm->benefits()->push($job->firm->benefits);
            $job->firm->assets()->sync($this->request->input('firm.assets'));
        }
        $job->creating(function (Job $model) use ($user) {
            $model->user_id = $user->id;
        });
        $job->save();
        $job->locations()->push($job->locations);
        $job->tags()->sync($this->tags($this->request));
        $job->features()->sync($this->features($this->request));
        stream($activity, (new Stream_Job)->map($job));
        return $job;
    }

    protected function getDefaultFeatures(Job $job, User $user): array
    {
        $features = $this->job->getDefaultFeatures($user->id);
        $models = [];
        foreach ($features as $feature) {
            $checked = (int)$feature['checked'];
            $pivot = $job->features()->newPivot([
                'checked' => $checked,
                'value'   => $checked ? ($feature['value'] ?? null) : null,
            ]);
            $models[] = Feature::query()->findOrNew($feature['id'])->setRelation('pivot', $pivot);
        }
        return $models;
    }

    protected function getUnpaidPayment(Job $job): ?Payment
    {
        return !$job->is_publish ? $job->getUnpaidPayment() : null;
    }

    protected function features(Request $request): array
    {
        $features = [];
        foreach ($request->input('features', []) as $feature) {
            $checked = (int)$feature['checked'];
            $features[$feature['id']] = ['checked' => $feature['checked'], 'value' => $checked ? ($feature['value'] ?? null) : null];
        }
        return $features;
    }

    protected function tags(Request $request): array
    {
        $tags = [];
        $order = 0;
        foreach ($request->input('tags', []) as $tag) {
            $model = Tag::query()->firstOrCreate(['name' => $tag['name']]);
            $tags[$model->id] = [
                'priority' => $tag['priority'] ?? 0,
                'order'    => ++$order,
            ];
        }
        return $tags;
    }
}
