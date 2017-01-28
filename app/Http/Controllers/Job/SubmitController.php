<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Events\JobWasSaved;
use Coyote\Firm\Benefit;
use Coyote\Http\Forms\Job\FirmForm;
use Coyote\Http\Forms\Job\JobForm;
use Coyote\Job;
use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\FirmRepositoryInterface as FirmRepository;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Repositories\Contracts\TagRepositoryInterface as TagRepository;
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
     * SubmitController constructor.
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
    public function getIndex(Request $request, $id = 0)
    {
        /** @var \Coyote\Job $job */
        if ($request->session()->has('job')) {
            // get form content from session and fill model
            $job = $request->session()->get('job');
        } else {
            $job = $this->job->findOrNew($id);
            $job->setDefaultUserId($this->userId);

            if (!$job->id) {
                // either load firm assigned to existing job offer or load user's default firm
                $firm = $this->loadDefaultFirm();
                $job->firm()->associate($firm);
            }

            $job->load(['tags', 'locations']);
        }

        $this->authorize('update', $job);

        if (!empty($job->firm->user_id)) {
            $this->authorize('update', $job->firm);
        }

        $form = $this->createForm(JobForm::class, $job);
        $request->session()->put('job', $job);

        $this->breadcrumb($job);

        $popularTags = $this->job->getPopularTags();
        $this->public += ['popular_tags' => $popularTags];

        return $this->view('job.submit.home', [
            'popular_tags'      => $popularTags,
            'form'              => $form,
            'form_errors'       => $form->errors() ? $form->errors()->toJson() : '[]',
            'job'               => $form->toJson(),
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
        $job = $request->session()->get('job');

        $form = $this->createForm(JobForm::class, $job);
        $form->validate();

        // only fillable columns! we don't want to set fields like "city" or "tags" because they don't exists.
        $job->fill($form->all());

        $request->session()->put('job', $job);

        return $this->next($request, redirect()->route('job.submit.firm'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function getFirm(Request $request)
    {
        /** @var \Coyote\Job $job */
        $job = $request->session()->get('job');

        // get all firms assigned to user...
        $firms = $this->getFirms($job->user_id)->toJson();
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
        $job = $request->session()->get('job');

        $form = $this->createForm(FirmForm::class, $job->firm);
        $form->validate();

        // if offer is private, we MUST remove firm data from session
        if ($form->get('is_private')->getValue()) {
            $job->firm()->dissociate();
        } else {
            $job->firm->setDefaultUserId($this->userId);
        }

        $request->session()->put('job', $job);

        return $this->next($request, redirect()->route('job.submit.preview'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getPreview(Request $request)
    {
        $job = $request->session()->get('job');

        $this->breadcrumb($job);

        debugbar()->debug($job);
        $tags = $job->tags->groupBy('pivot.priority');

        $parser = app('parser.job');

        foreach (['description', 'requirements', 'recruitment'] as $name) {
            if (!empty($job[$name])) {
                $job[$name] = $parser->parse($job[$name]);
            }
        }

        if (!empty($firm['description'])) {
            $firm['description'] = $parser->parse($firm['description']);
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
        $job = clone $request->session()->get('job');

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

            if ($job->firm_id) {
                $job->firm->save();

                $job->firm->benefits()->delete();
                $job->firm->benefits()->saveMany($job->firm->benefits);
            }

            $job->save();

            $job->locations()->delete();
            $job->locations()->saveMany($job->locations);

            $job->tags()->sync($tags);

            event(new JobWasSaved($job));
            $request->session()->forget(['job']);

            $parser = app('parser.job');
            $job->description = $parser->parse($job->description);

            stream($activity, (new Stream_Job)->map($job));
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
     * @param int $userId
     * @return mixed
     */
    private function getFirms($userId)
    {
        // get all firms assigned to user...
        $firms = $this->firm->findAllBy('user_id', $userId);

        /** @var \Coyote\Firm $firm */
        foreach ($firms as &$firm) {
            $firm->thumbnail = $firm->logo->getFilename() ? (string) $firm->logo->url() : cdn('img/logo-gray.png');
        }

        return $firms;
    }

    /**
     * Load user's default firm
     *
     * @return \Coyote\Firm
     */
    private function loadDefaultFirm()
    {
        $firm = $this->firm->findBy('user_id', $this->userId);

        return $firm;
    }
}
