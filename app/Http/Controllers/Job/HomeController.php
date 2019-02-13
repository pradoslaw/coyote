<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Resources\JobCollection;
use Coyote\Http\Resources\JobResource;
use Coyote\Http\Resources\TagResource;
use Coyote\Job\Preferences;
use Coyote\Repositories\Contracts\TagRepositoryInterface as TagRepository;
use Coyote\Repositories\Criteria\EagerLoading;
use Coyote\Repositories\Criteria\EagerLoadingWithCount;
use Coyote\Repositories\Criteria\Job\IncludeSubscribers;
use Coyote\Repositories\Criteria\Tag\ForCategory;
use Coyote\Services\Elasticsearch\Builders\Job\SearchBuilder;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Tag;
use Illuminate\Http\Request;
use Coyote\Job;
use Coyote\Currency;
use Illuminate\Pagination\LengthAwarePaginator;

class HomeController extends BaseController
{
    /**
     * @var array|mixed
     */
    private $preferences = [];

    /**
     * @var TagRepository
     */
    private $tag;

    /**
     * @param JobRepository $job
     * @param TagRepository $tag
     */
    public function __construct(JobRepository $job, TagRepository $tag)
    {
        parent::__construct($job);
        $this->tag = $tag;

        $this->middleware(function (Request $request, $next) {
            $this->builder = new SearchBuilder($request);

            return $next($request);
        });
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return $this->load();
    }

    /**
     * @param $name
     * @return \Illuminate\View\View
     */
    public function city($name)
    {
        $this->builder->city->addCity($name);

        return $this->load();
    }

    /**
     * @param $name
     * @return \Illuminate\View\View
     */
    public function tag($name)
    {
        $this->builder->tag->addTag($name);

        return $this->load();
    }

    /**
     * @param $slug
     * @return \Illuminate\View\View
     */
    public function firm($slug)
    {
        $this->builder->addFirmFilter($slug);

        return $this->load(['firm' => $slug]);
    }

    /**
     * @return \Illuminate\View\View
     */
    public function remote()
    {
        $this->builder->addRemoteFilter();

        return $this->load();
    }

    /**
     * @return \Illuminate\View\View
     */
    public function my()
    {
        $this->builder->addUserFilter($this->userId);

        return $this->load();
    }

    /**
     * @return \Illuminate\View\View
     */
    private function load()
    {
        $this->preferences = new Preferences($this->getSetting('job.preferences'));
        $this->builder->setPreferences($this->preferences);

        // get only tags belong to specific category
        $this->tag->pushCriteria(new ForCategory(Tag\Category::LANGUAGE));

        // only tags with logo
        $tags = $this->tag->all()->filter(function (Tag $tag) {
            return $tag->logo->getFilename() !== null;
        });

        $this->builder->setLanguages($tags->pluck('name')->toArray());

        $this->builder->boostLocation($this->request->attributes->get('geocode'));
        $this->request->session()->put('current_url', $this->request->fullUrl());

        $this->builder->setSort($this->request->input('sort', $this->builder::DEFAULT_SORT));

        $result = $this->job->search($this->builder);

        // keep in mind that we return data by calling getSource(). This is important because
        // we want to pass collection to the twig (not raw php array)
        $source = $result->getSource();

        ///////////////////////////////////////////////////////////////////

        $this->job->pushCriteria(new EagerLoading(['firm:id,name,slug,logo', 'locations', 'tags', 'currency']));
        $this->job->pushCriteria(new EagerLoadingWithCount(['comments']));
        $this->job->pushCriteria(new IncludeSubscribers($this->userId));

//        $ids = $result->getAggregationHits('premium_listing', true)->merge($source)->pluck('id')->toArray();
        $ids = $source->pluck('id')->toArray();
        $jobs = $this->job->findManyWithOrder($ids);

        $pagination = new LengthAwarePaginator(
            $jobs,
            $result->total(),
            SearchBuilder::PER_PAGE,
            LengthAwarePaginator::resolveCurrentPage(),
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        $pagination->appends($this->request->except('page'));

        $subscribes = [];

        if ($this->userId) {
//            $subscribes = JobResource::collection($this->job->subscribes($this->userId))->toArray($this->request);
        }

        $input = [
            'tags'          => $this->builder->tag->getTags(),
            'cities'        => array_map('mb_strtolower', $this->builder->city->getCities()),
            'city'          => array_first($this->builder->city->getCities()),
            'remote'        => $this->request->filled('remote') || $this->request->route()->getName() === 'job.remote',
            'q'             => $this->request->input('q'),
            'sort'          => $this->builder->getSort(),
            'salary'        => $this->request->input('salary'),
            'currency'      => $this->request->input('currency', Currency::PLN)
        ];

        $data = [
//            'rates_list'        => Job::getRatesList(),
//            'employment_list'   => Job::getEmploymentList(),

//            'preferences'       => $this->preferences,
//            'listing'           => $listing,
//            'premium_listing'   => $result->getAggregationHits('premium_listing', true),
//            'aggregations'      => $aggregations,

            'subscribes'        => $subscribes,
            'input'             => $input,


            'tags'              => TagResource::collection($tags)->toArray($this->request),
            'jobs'              => json_decode((new JobCollection($pagination))->response()->getContent())
        ];

        if ($this->request->wantsJson()) {
            return response()->json($data);
        }

        return $this->view('job.home', $data + ['currencies_list' => Currency::getCurrenciesList()]);
    }
}
