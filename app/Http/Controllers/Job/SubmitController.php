<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Events\JobWasSaved;
use Coyote\Firm\Benefit;
use Coyote\Http\Forms\Job\FirmForm;
use Coyote\Http\Forms\Job\JobForm;
use Coyote\Http\Transformers\FirmWithBenefits;
use Coyote\Job;
use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\FirmRepositoryInterface as FirmRepository;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Repositories\Contracts\TagRepositoryInterface as TagRepository;
use Coyote\Repositories\Criteria\EagerLoading;
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
     * @var TagRepository
     */
    private $tag;

    /**
     * @param JobRepository $job
     * @param FirmRepository $firm
     * @param TagRepository $tag
     */
    public function __construct(
        JobRepository $job,
        FirmRepository $firm,
        TagRepository $tag
    ) {
        parent::__construct();

        $this->middleware('job.revalidate', ['except' => ['postTag', 'getFirmPartial']]);
        $this->middleware('job.session', ['except' => ['getIndex', 'postIndex', 'postTag', 'getFirmPartial']]);

        $this->breadcrumb->push('Praca', route('job.home'));

        $this->job = $job;
        $this->firm = $firm;
        $this->tag = $tag;
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function getIndex(Request $request, $id = null)
    {
        /** @var \Coyote\Job $job */
        if ($id === null && $request->session()->has(Job::class)) {
            // get form content from session
            $job = $request->session()->get(Job::class);
        } else {
            $job = $this->job->findOrNew($id);
            $job->setDefaultUserId($this->userId);

            // load default firm regardless of offer is private or not
            if (!$job->firm_id) {
                $firm = $this->loadDefaultFirm();
                $firm->is_private = $job->exists && !$job->firm_id;

                $job->firm()->associate($firm);
            }

            $job->load(['tags', 'locations']);
        }

        $this->authorize('update', $job);
        $this->authorize('update', $job->firm);

        $form = $this->createForm(JobForm::class, $job);
        $request->session()->put(Job::class, $job);

        $this->breadcrumb($job);

        $popularTags = $this->job->getPopularTags();
        $this->public += ['popular_tags' => $popularTags];

        return $this->view('job.submit.home', [
            'popular_tags'      => $popularTags,
            'form'              => $form,
            'form_errors'       => $form->errors() ? $form->errors()->toJson() : '[]',
            'job'               => $form->toJson(),
            // firm information (in order to show firm nam on the button)
            'firm'              => $job->firm,
            'tags'              => collect($form->get('tags')->getChildrenValues())->toJson()
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postIndex(Request $request)
    {
        /** @var \Coyote\Job $job */
        $job = $request->session()->get(Job::class);

        $form = $this->createForm(JobForm::class, $job);
        $form->validate();

        // only fillable columns! we don't want to set fields like "city" or "tags" because they don't exists.
        $job->fill($form->all());

        $request->session()->put(Job::class, $job);

        return $this->next($request, redirect()->route('job.submit.firm'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function getFirm(Request $request)
    {
        /** @var \Coyote\Job $job */
        $job = clone $request->session()->get(Job::class);

        // get all firms assigned to user...
        $this->firm->pushCriteria(new EagerLoading('benefits'));
        $firms = fractal($this->firm->findAllBy('user_id', $job->user_id), new FirmWithBenefits())->toJson();

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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postFirm(Request $request)
    {
        /** @var \Coyote\Job $job */
        $job = $request->session()->get(Job::class);

        $form = $this->createForm(FirmForm::class, $job->firm);
        $form->validate();

        $request->session()->put(Job::class, $job);

        return $this->next($request, redirect()->route('job.submit.preview'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getPreview(Request $request)
    {
        /** @var \Coyote\Job $job */
        $job = clone $request->session()->get(Job::class);

        $this->breadcrumb($job);

        $tags = $job->tags->groupBy('pivot.priority');

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
            'tags'              => $tags,
            'ratesList'         => Job::getRatesList(),
            'employmentList'    => Job::getEmploymentList()
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request)
    {
        /** @var \Coyote\Job $job */
        $job = clone $request->session()->get(Job::class);

        $this->authorize('update', $job);

        $tags = [];
        if ($job->tags->count()) {
            $order = 0;

            foreach ($job->tags as $tag) {
                $model = $tag->firstOrCreate(['name' => $tag->name]);

                $tags[$model->id] = [
                    'priority'  => $tag->pivot->priority ?? 0,
                    'order'     => ++$order
                ];
            }
        }

        $this->transaction(function () use (&$job, $request, $tags) {
            $activity = $job->id ? Stream_Update::class : Stream_Create::class;

            if ($job->firm->is_private) {
                $job->firm()->dissociate();
            }

            if ($job->firm_id) {
                $job->firm->syncOriginalAttribute('id');
                // user might click on "add new firm" button in form. make sure user_id is set up.
                $job->firm->setDefaultUserId($this->userId);

                $this->authorize('update', $job->firm);

                // reassociate job with firm. user could change firm, that's why we have to do it again.
                $job->firm()->associate($job->firm);

                $job->firm->save();

                $job->firm->benefits()->delete();
                $job->firm->benefits()->saveMany($job->firm->benefits);
            }

            $job->save();

            $job->locations()->delete();
            $job->locations()->saveMany($job->locations);

            $job->tags()->sync($tags);

            event(new JobWasSaved($job));

            $parser = app('parser.job');
            $job->description = $parser->parse($job->description);

            stream($activity, (new Stream_Job)->map($job));

            $request->session()->forget(Job::class);
        });

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
     * @param \Illuminate\Http\RedirectResponse $next
     * @return \Illuminate\Http\RedirectResponse
     */
    private function next(Request $request, $next)
    {
        if ($request->get('done')) {
            return $this->save($request);
        }

        return $next;
    }

    /**
     * Load user's default firm
     *
     * @return \Coyote\Firm
     */
    private function loadDefaultFirm()
    {
        $firm = $this->firm->findBy('user_id', $this->userId);

        if (!$firm) {
            /** @var \Coyote\Firm $firm */
            $firm = $this->firm->newInstance();
            $firm->setDefaultUserId($this->userId);
        }

        return $firm;
    }
}
