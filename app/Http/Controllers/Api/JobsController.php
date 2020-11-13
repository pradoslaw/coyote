<?php

namespace Coyote\Http\Controllers\Api;

use Coyote\Events\JobWasSaved;
use Coyote\Events\PaymentPaid;
use Coyote\Firm;
use Coyote\Http\Factories\MediaFactory;
use Coyote\Http\Resources\Api\JobApiResource;
use Coyote\Repositories\Contracts\CouponRepositoryInterface as CouponRepository;
use Illuminate\Database\Connection;
use Illuminate\Http\Resources\Json\Resource;
use Coyote\Http\Requests\Job\ApiRequest;
use Coyote\Job;
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
    use AuthorizesRequests, SubmitsJob, MediaFactory;

    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $this->job->pushCriteria(new EagerLoading(['firm', 'locations', 'tags', 'currency']));
        $this->job->pushCriteria(new EagerLoadingWithCount(['comments']));
        $this->job->pushCriteria(new PriorDeadline());
        $this->job->pushCriteria(new OnlyPublished());
        $this->job->pushCriteria(new Sort('jobs.id', Sort::DESC));

        $this->job->applyCriteria();

        $data = $this->job->paginate();

        return JobApiResource::collection($data);
    }

    /**
     * @param Job $job
     * @return JobApiResource
     */
    public function show(Job $job)
    {
        Resource::withoutWrapping();

        $this->job->pushCriteria(new EagerLoading(['firm', 'locations', 'tags', 'currency']));
        $this->job->pushCriteria(new EagerLoading('features'));
        $this->job->pushCriteria(new EagerLoadingWithCount(['comments']));

        return new JobApiResource($job);
    }

    /**
     * @param Job $job
     * @param ApiRequest $request
     * @param Auth $auth
     * @param $repository CouponRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function save(Job $job, ApiRequest $request, Auth $auth, CouponRepository $repository)
    {
        $user = $auth->guard('api')->user();

        JobApiResource::$parser = app('parser.job');

        if (!$job->exists) {
            $job = $this->loadDefaults($job, $user);
            $job->firm()->dissociate(); // default setting with API: firm is not assigned to the job
        }

        $job->fill(array_merge(['tags' => [], 'locations' => []], $request->all()));

        if ($request->filled('firm.name')) {
            $firm = $this->firm->loadFirm($user->id, $request->input('firm.name'));

            $firm->fill($request->input('firm'));

            if ($request->has('firm.logo')) {
                $media = $this->getMediaFactory()->make('logo')->put(base64_decode($request->input('firm.logo')));
                $firm->logo = $media->getFilename();
            }

            Firm::creating(function (Firm $model) use ($user) {
                $model->user_id = $user->id;
            });

            $job->firm()->associate($firm);
        }

        $job->load('plan'); // reload plan relation as it might has changed
        $this->request = $request;

        app(Connection::class)->transaction(function () use ($job, $repository, $user) {
            $this->saveRelations($job, $user);

            if ($job->wasRecentlyCreated || !$job->is_publish) {
                $coupon = $repository->findCoupon($user->id, $job->plan->gross_price);

                $job->payments()->create(['plan_id' => $job->plan_id, 'days' => $job->plan->length, 'coupon_id' => $coupon->id]);
            }

            event(new JobWasSaved($job)); // we don't queue listeners for this event
        });

        if ($payment = $this->getUnpaidPayment($job)) {
            event(new PaymentPaid($payment));
        }

        return response(new JobApiResource($job), $job->wasRecentlyCreated ? 201 : 200);
    }
}
