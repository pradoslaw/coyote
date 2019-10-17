<?php

namespace Coyote\Http\Controllers\Api;

use Coyote\Events\PaymentPaid;
use Coyote\Http\Factories\MediaFactory;
use Coyote\Repositories\Contracts\CouponRepositoryInterface as CouponRepository;
use Illuminate\Http\Resources\Json\Resource;
use Coyote\Http\Requests\Job\ApiRequest;
use Coyote\Http\Resources\JobResource;
use Coyote\Job;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
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
     * @param JobRepository $job
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $this->job->pushCriteria(new EagerLoading(['firm:id,name,slug,logo', 'locations', 'tags', 'currency']));
        $this->job->pushCriteria(new EagerLoadingWithCount(['comments']));
        $this->job->pushCriteria(new PriorDeadline());
        $this->job->pushCriteria(new OnlyPublished());
        $this->job->pushCriteria(new Sort('jobs.id', Sort::DESC));

        $this->job->applyCriteria();

        $data = $this->job->paginate();

        return JobResource::collection($data);
    }

    /**
     * @param Job $job
     * @return JobResource
     */
    public function show(Job $job)
    {
        Resource::withoutWrapping();

        $this->job->pushCriteria(new EagerLoading(['firm:id,name,slug,logo', 'locations', 'tags', 'currency']));
        $this->job->pushCriteria(new EagerLoading('features'));
        $this->job->pushCriteria(new EagerLoadingWithCount(['comments']));

        return new JobResource($job);
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

        $job = $this->loadDefaults($job, $user);
        $job->firm()->dissociate(); // default setting with API: firm is not assigned to the job

        $this->authorizeForUser($user, 'update', $job);

        $job->fill($request->all());

        if ($request->has('firm.name')) {
            $firm = $this->firm->loadFirm($user->id, $request->input('firm.name'));

            $firm->fill($request->input('firm'));

            if ($request->has('firm.logo')) {
                $media = $this->getMediaFactory()->make('logo')->put(base64_decode($request->input('firm.logo')));
                $firm->logo = $media->getFilename();
            }

            $job->firm()->associate($firm);
        }

        $job->load('plan'); // reload plan relation as it might has changed

        $this->saveWithTransaction($job, $user);

        if ($payment = $this->getUnpaidPayment($job)) {
            $coupon = $repository->findCoupon($user->id, $job->plan->gross_price);

            $payment->coupon_id = $coupon->id;
            $payment->save();

            event(new PaymentPaid($payment));
        }

        return response(new JobResource($job), $job->wasRecentlyCreated ? 201 : 200);
    }
}
