<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Events\JobWasSaved;
use Coyote\Firm;
use Coyote\Firm\Benefit;
use Coyote\Http\Forms\Job\FirmForm;
use Coyote\Http\Forms\Job\JobForm;
use Coyote\Http\Resources\Firm as FirmResource;
use Coyote\Job;
use Coyote\Http\Controllers\Controller;
use Coyote\Notifications\Job\CreatedNotification;
use Coyote\Repositories\Contracts\FirmRepositoryInterface as FirmRepository;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Repositories\Contracts\PlanRepositoryInterface as PlanRepository;
use Coyote\Repositories\Criteria\EagerLoading;
use Coyote\Services\Job\Draft;
use Coyote\Services\Job\Loader;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Illuminate\Http\Request;
use Coyote\Services\Stream\Objects\Job as Stream_Job;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;

class SubmitController extends Controller
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

        $this->middleware('job.forget');
        $this->middleware('job.session', ['except' => ['getIndex']]);

        $this->breadcrumb->push('Praca', route('job.home'));

        $this->job = $job;
        $this->firm = $firm;
        $this->plan = $plan;
    }

    /**
     * @param Draft $draft
     * @param Loader $loader
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function getIndex(Draft $draft, Loader $loader, $id = null)
    {
        /** @var \Coyote\Job $job */
        if ($id === null && $draft->has(Job::class)) {
            // get form content from session
            $job = $draft->get(Job::class);
        } else {
            $job = $this->job->findOrNew($id);
            abort_if($job->exists && $job->is_expired, 404);

            $job = $loader->init($job);
        }

        $this->authorize('update', $job);
        $this->authorize('update', $job->firm);

        $form = $this->createForm(JobForm::class, $job);
        $draft->put(Job::class, $job);

        $this->breadcrumb($job);

        return $this->view('job.submit.home', [
            'popular_tags'      => $this->job->getPopularTags(),
            'form'              => $form,
            'form_errors'       => $form->errors() ? $form->errors()->toJson() : '[]',
            'job'               => $form->toJson(),
            // firm information (in order to show firm nam on the button)
            'firm'              => $job->firm,
            // is plan is still going on?
            'is_plan_ongoing'   => $job->is_publish,
            'plans'             => $this->plan->active()->toJson()
        ]);
    }

    /**
     * @param Request $request
     * @param Draft $draft
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postIndex(Request $request, Draft $draft)
    {
        /** @var \Coyote\Job $job */
        $job = clone $draft->get(Job::class);

        $form = $this->createForm(JobForm::class, $job);
        $form->validate();

        // only fillable columns! we don't want to set fields like "city" or "tags" because they don't really exists in db.
        $job->fill($form->all());

        $draft->put(Job::class, $job);

        return $this->next($request, $draft, redirect()->route('job.submit.firm'));
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
        $this->firm->pushCriteria(new EagerLoading(['benefits', 'industries', 'gallery']));

        $firms = json_encode(FirmResource::collection($this->firm->findAllBy('user_id', $job->user_id))->toArray($this->request));

        $this->breadcrumb($job);

        $form = $this->createForm(FirmForm::class, $job->firm);

        return $this->view('job.submit.firm')->with([
            'job'               => $job,
            'firm'              => $form->toJson(),
            'firms'             => $firms,
            'form'              => $form,
            'form_errors'       => $form->errors() ? $form->errors()->toJson() : '[]',
            'benefits'          => $form->get('benefits')->getChildrenValues(),
            'default_benefits'  => Benefit::getBenefitsList(), // default benefits,
        ]);
    }

    /**
     * @param Request $request
     * @param Draft $draft
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postFirm(Request $request, Draft $draft)
    {
        /** @var \Coyote\Job $job */
        $job = $draft->get(Job::class);

        $form = $this->createForm(FirmForm::class, $job->firm);
        $form->validate();

        if ($job->firm->exists) {
            // syncOriginalAttribute() is important if user changes firm
            $job->firm->syncOriginalAttribute('id');
        }

        $draft->put(Job::class, $job);

        return $this->next($request, $draft, redirect()->route('job.submit.preview'));
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
     * @param Draft $draft
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function save(Draft $draft)
    {
        /** @var \Coyote\Job $job */
        $job = clone $draft->get(Job::class);

        $this->authorize('update', $job);

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

        $this->transaction(function () use (&$job, $draft, $tags, $features) {
            $activity = $job->id ? Stream_Update::class : Stream_Create::class;

            if ($job->firm->is_private) {
                $job->firm()->dissociate();
            // firm name is required to save firm
            } elseif ($job->firm->name) {
                // user might click on "add new firm" button in form. make sure user_id is set up.
                $job->firm->setDefaultUserId($this->userId);

                $this->authorize('update', $job->firm);

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

            if ($job->wasRecentlyCreated || !$job->is_publish) {
                $job->payments()->create(['plan_id' => $job->plan_id, 'days' => $job->plan->length]);
            }

            stream($activity, (new Stream_Job)->map($job));
            $draft->forget();

            event(new JobWasSaved($job)); // we don't queue listeners for this event

            return $job;
        });

        if ($job->wasRecentlyCreated) {
            $job->user->notify(new CreatedNotification($job));
        }

        $paymentUuid = $job->getPaymentUuid();
        if ($paymentUuid !== null) {
            return redirect()
                ->route('job.payment', [$paymentUuid])
                ->with('success', 'Oferta została dodana, lecz nie jest jeszcze promowana. Uzupełnij poniższy formularz, aby zakończyć.');
        }

        return redirect()->to(UrlBuilder::job($job))->with('success', 'Oferta została prawidłowo dodana.');
    }

    /**
     * @param $job
     */
    private function breadcrumb($job)
    {
        if (empty($job['id'])) {
            $this->breadcrumb->push('Wystaw ofertę pracy', route('job.submit'));
        } else {
            $this->breadcrumb->push($job['title'], route('job.offer', [$job['id'], $job['slug']]));
            $this->breadcrumb->push('Edycja oferty', route('job.submit'));
        }
    }

    /**
     * @param Request $request
     * @param Draft $draft
     * @param \Illuminate\Http\RedirectResponse $next
     * @return \Illuminate\Http\RedirectResponse
     */
    private function next(Request $request, Draft $draft, $next)
    {
        if ($request->get('done')) {
            return $this->save($draft);
        }

        return $next;
    }
}
