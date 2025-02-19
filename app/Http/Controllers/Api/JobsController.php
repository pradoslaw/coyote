<?php
namespace Coyote\Http\Controllers\Api;

use Coyote\Events\JobWasSaved;
use Coyote\Events\PaymentPaid;
use Coyote\Firm;
use Coyote\Http\Factories\MediaFactory;
use Coyote\Http\Requests\Job\ApiRequest;
use Coyote\Http\Resources\Api\JobApiResource;
use Coyote\Job;
use Coyote\Repositories\Criteria\EagerLoading;
use Coyote\Repositories\Criteria\EagerLoadingWithCount;
use Coyote\Repositories\Criteria\Job\OnlyPublished;
use Coyote\Repositories\Criteria\Job\PriorDeadline;
use Coyote\Repositories\Criteria\Sort;
use Coyote\Repositories\Eloquent\CouponRepository;
use Coyote\Services\Job\SubmitsJob;
use Coyote\User;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Database\Connection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class JobsController extends Controller
{
    use AuthorizesRequests, SubmitsJob, MediaFactory;

    protected Request $request;

    public function index(): ResourceCollection
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

    public function show(Job $job): JobApiResource
    {
        JobApiResource::withoutWrapping();
        $this->job->pushCriteria(new EagerLoading(['firm', 'locations', 'tags', 'currency']));
        $this->job->pushCriteria(new EagerLoading('features'));
        $this->job->pushCriteria(new EagerLoadingWithCount(['comments']));
        return new JobApiResource($job);
    }

    public function save(Job $job, ApiRequest $request, Auth $auth, CouponRepository $repository): Response
    {
        /** @var User $user */
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
                $coupon = $repository->findCoupon($user->id, $job->plan->price);
                $job->payments()->create([
                    'plan_id'   => $job->plan_id,
                    'days'      => $job->plan->length,
                    'coupon_id' => $coupon->id ?? null,
                ]);
            }
            event(new JobWasSaved($job)); // we don't queue listeners for this event
        });
        $payment = $this->getUnpaidPayment($job);
        if ($payment) {
            event(new PaymentPaid($payment));
        }
        return response(new JobApiResource($job), $job->wasRecentlyCreated ? 201 : 200);
    }
}
