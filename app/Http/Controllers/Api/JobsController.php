<?php

namespace Coyote\Http\Controllers\Api;

use Coyote\Events\PaymentPaid;
use Coyote\Plan;
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

/**
 * @OA\Info(title="My First API", version="0.1")
 */
class JobsController extends Controller
{
    use AuthorizesRequests, SubmitsJob;

    /**
     * @OA\Get(
     *     path="/v1/jobs",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false
     *     ),
     *     @OA\Response(response="200", description="A list with all jobs")
     * )
     */
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
     * @OA\Post(
     *     path="/v1/jobs",
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="Page number",
     *         required=false
     *     ),
     *     @OA\Response(response="201", description="Newly created job"),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */
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
            $job->firm()->associate($firm);
        }

        $job->load('plan'); // reload plan relation as it might has changed

        $this->saveWithTransaction($job, $user);

        if ($payment = $this->getUnpaidPayment($job)) {
            $coupon = $repository->findCoupon($user->id, $job->plan->gross_price);

            $payment->coupon_id = $coupon->id;
            $payment->save();

            $coupon->delete();

            event(new PaymentPaid($payment));
        }

        return response(new JobResource($job), $job->wasRecentlyCreated ? 201 : 200);
    }
}
