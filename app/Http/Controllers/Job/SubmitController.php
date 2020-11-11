<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Currency;
use Coyote\Events\JobWasSaved;
use Coyote\Firm;
use Coyote\Firm\Benefit;
use Coyote\Http\Requests\Job\FirmRequest;
use Coyote\Http\Requests\Job\JobRequest;
use Coyote\Http\Resources\FirmFormResource as FirmResource;
use Coyote\Http\Resources\JobFormResource;
use Coyote\Job;
use Coyote\Http\Controllers\Controller;
use Coyote\Notifications\Job\CreatedNotification;
use Coyote\Repositories\Contracts\FirmRepositoryInterface as FirmRepository;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Repositories\Contracts\PlanRepositoryInterface as PlanRepository;
use Coyote\Repositories\Criteria\EagerLoading;
use Coyote\Services\Job\SubmitsJob;
use Coyote\Services\UrlBuilder;
use Illuminate\Http\Request;

class SubmitController extends Controller
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
     * @return \Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getIndex(Job $job)
    {
        if (!$job->exists) {
            $job = $this->loadDefaults($job, $this->auth);
        }

        $this->authorize('update', $job);

        if ($job->firm) {
            $this->authorize('update', $job->firm);
        }

        $this->breadcrumb($job);

        return $this->view('job.submit.home', [
            'popular_tags'      => $this->job->getPopularTags(),
            'job'               => new JobFormResource($job),
            // firm information (in order to show firm nam on the button)
            'firm'              => $job->firm,
            // is plan is still going on?
            'is_plan_ongoing'   => $job->is_publish,
            'plans'             => $this->plan->active()->toJson(),
            'currencies'        => Currency::all()
        ]);
    }

    /**
     * @param Draft $draft
     * @return \Illuminate\View\View
     */
    public function getFirm(Draft $draft)
    {
        /** @var \Coyote\Job $job */
        $job = clone $draft->get(Job::class);

        // get all firms assigned to user...
        $this->firm->pushCriteria(new EagerLoading(['benefits', 'gallery']));

        $firms = FirmResource::collection($this->firm->findAllBy('user_id', $job->user_id));

        $this->breadcrumb($job);

        return $this->view('job.submit.firm')->with([
            'job'               => $job,
            'firm'              => new FirmResource($job->firm),
            'firms'             => $firms,
            'default_benefits'  => Benefit::getBenefitsList(), // default benefits,
            'employees'         => Firm::getEmployeesList(),
            'founded'           => Firm::getFoundedList()
        ]);
    }

    /**
     * @param Request $request
     * @param Draft $draft
     * @return string
     */
    public function postFirm(FirmRequest $request, Draft $draft)
    {
        /** @var \Coyote\Job $job */
        $job = $draft->get(Job::class);

        $job->firm->fill($request->all());

        // new firm has empty ID.
        if (empty($request->input('id'))) {
            $job->firm->exists = false;

            unset($job->firm->id);
        } else {
            // assign firm id. id is not fillable - that's why we must set it directly.
            $job->firm->id = (int) $request->input('id');
        }

        if ($job->firm->exists) {
            // syncOriginalAttribute() is important if user changes firm
            $job->firm->syncOriginalAttribute('id');
        }

        $draft->put(Job::class, $job);

        return $this->next($request, $draft, route('job.submit.preview'));
    }

    /**
     * @param Draft $draft
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getPreview(Draft $draft)
    {
        /** @var \Coyote\Job $job */
        $job = clone $draft->get(Job::class);

        $this->breadcrumb($job);

        $tags = $job->tags()->orderBy('priority', 'DESC')->with('category')->get()->groupCategory();

        $parser = app('parser.job');

        foreach (['description', 'requirements', 'recruitment'] as $name) {
            if (!empty($job[$name])) {
                $job[$name] = $parser->parse($job[$name]);
            }
        }

        if ($job->firm->is_private) {
            $job->firm()->dissociate();
        }

        if (!empty($job->firm->description)) {
            $job->firm->description = $parser->parse($job->firm->description);
        }

        return $this->view('job.submit.preview', [
            'job'               => $job,
            'firm'              => $job->firm ? $job->firm->toJson() : '{}',
            'tags'              => $tags,
            'rates_list'        => Job::getRatesList(),
            'seniority_list'    => Job::getSeniorityList(),
            'employment_list'   => Job::getEmploymentList(),
            'employees_list'    => Firm::getEmployeesList(),
        ]);
    }

    /**
     * @param JobRequest $request
     * @param Job $job
     * @return string
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function save(JobRequest $request, Job $job)
    {
        $job->fill($request->all());

        if ($job->exists) {
            $this->authorize('update', $job);
        }

        $this->transaction(function () use ($job) {
            $this->prepareAndSave($job, $this->auth);

            if ($job->wasRecentlyCreated || !$job->is_publish) {
                $job->payments()->create(['plan_id' => $job->plan_id, 'days' => $job->plan->length]);
            }

            event(new JobWasSaved($job)); // we don't queue listeners for this event

//            $draft->forget();
        });

        if ($job->wasRecentlyCreated) {
            $job->user->notify(new CreatedNotification($job));
        }

        if ($unpaidPayment = $this->getUnpaidPayment($job)) {
            session()->flash('success', 'Oferta została dodana, lecz nie jest jeszcze promowana. Uzupełnij poniższy formularz, aby zakończyć.');

            return route('job.payment', [$unpaidPayment]);
        }

        session()->flash('success', 'Oferta została prawidłowo dodana.');

        return UrlBuilder::job($job);
    }

    /**
     * @param $job
     */
    private function breadcrumb($job)
    {
        $this->breadcrumb->push('Praca', route('job.home'));

        if (empty($job['id'])) {
            $this->breadcrumb->push('Wystaw ofertę pracy', route('job.submit'));
        } else {
            $this->breadcrumb->push($job['title'], route('job.offer', [$job['id'], $job['slug']]));
            $this->breadcrumb->push('Edycja oferty', route('job.submit'));
        }
    }
}
