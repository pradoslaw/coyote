<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Job\Preferences;
use Coyote\Repositories\Contracts\TagRepositoryInterface as TagRepository;
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
     * @param array $data
     * @return \Illuminate\View\View
     */
    private function load(array $data = [])
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

        $this->builder->setSort($this->request->input('sort', $this->request->filled('q') ? '_score' : $this->builder::DEFAULT_SORT));

        $result = $this->job->search($this->builder);

        // keep in mind that we return data by calling getSource(). This is important because
        // we want to pass collection to the twig (not raw php array)
        $listing = $result->getSource();

        $context = !$this->request->filled('q') ? 'global.' : '';
        $aggregations = [
            'cities'        => $result->getAggregationCount("${context}locations.locations_city_original"),
            'tags'          => $result->getAggregationCount("${context}tags"),
            'remote'        => $result->getAggregationCount("${context}remote")
        ];

        $pagination = new LengthAwarePaginator(
            $listing,
            $result->total(),
            SearchBuilder::PER_PAGE,
            LengthAwarePaginator::resolveCurrentPage(),
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        $pagination->appends($this->request->except('page'));

        $subscribes = [];

        if ($this->userId) {
            $subscribes = $this->job->subscribes($this->userId);
        }

        $selected = [
            'tags'          => $this->builder->tag->getTags(),
            'cities'        => array_map('mb_strtolower', $this->builder->city->getCities()),
            'remote'        => $this->request->filled('remote') || $this->request->route()->getName() === 'job.remote'
        ];

        return $this->view('job.home', array_merge($data, [
            'rates_list'        => Job::getRatesList(),
            'employment_list'   => Job::getEmploymentList(),
            'currency_list'     => Currency::getCurrenciesList(),
            'preferences'       => $this->preferences,
            'listing'           => $listing,
            'premium_listing'   => $result->getAggregationHits('premium_listing', true),
            'aggregations'      => $aggregations,
            'pagination'        => $pagination,
            'subscribes'        => $subscribes,
            'selected'          => $selected,
            'sort'              => $this->builder->getSort(),
            'tags'              => $tags->keyBy('name')
        ]));
    }
}
