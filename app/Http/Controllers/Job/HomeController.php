<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Job\Preferences;
use Coyote\Services\Elasticsearch\Builders\Job\SearchBuilder;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
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
     * @param JobRepository $job
     */
    public function __construct(JobRepository $job)
    {
        parent::__construct($job);

        $this->middleware('geocode');

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
        $this->preferences = new Preferences($this->getSetting('job.preferences'));

        $this->tab = $this->request->get('tab', $this->getSetting('job.tab', self::TAB_FILTERED));
        $validator = $this->getValidationFactory()->make(
            $this->request->all(),
            ['tab' => 'sometimes|in:' . self::TAB_ALL . ',' . self::TAB_FILTERED]
        );

        if ($validator->fails()) {
            $this->tab = self::TAB_FILTERED;
        }

        if ($this->request->has('tab')) {
            $this->setSetting('job.tab', $this->tab);
        }

        // if user want to filter job offers, we MUST select "all" tab
        if ($this->notEmpty($this->request, ['q', 'city', 'remote', 'tag'])) {
            $this->tab = self::TAB_ALL;
        }

        if ($this->tab == self::TAB_FILTERED) {
            $this->builder->setPreferences($this->preferences);
        }

        $this->builder->boostLocation($this->request->attributes->get('geocode'));
        $this->request->session()->put('current_url', $this->request->fullUrl());

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
     * @param $name
     * @return \Illuminate\View\View
     */
    public function firm($name)
    {
        $this->builder->addFirmFilter($name);

        return $this->load();
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
    private function load()
    {
        $result = $this->job->search($this->builder);

        // keep in mind that we return data by calling getSource(). This is important because
        // we want to pass collection to the twig (not raw php array)
        $jobs = $result->getSource();

        $context = !$this->request->has('q') ? 'global.' : '';
        $aggregations = [
            'cities'        => $result->getAggregations("${context}locations.locations_city_original"),
            'tags'          => $result->getAggregations("${context}tags"),
            'remote'        => $result->getAggregations("${context}remote")
        ];

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
            $subscribes = $this->job->subscribes($this->userId);
        }

        $selected = [];
        if ($this->tab !== self::TAB_FILTERED) {
            $selected = [
                'tags'          => $this->builder->tag->getTags(),
                'cities'        => array_map('mb_strtolower', $this->builder->city->getCities()),
                'remote'        => $this->request->has('remote') || $this->request->route()->getName() === 'job.remote'
            ];
        }

        return $this->view('job.home', [
            'rates_list'        => Job::getRatesList(),
            'employment_list'   => Job::getEmploymentList(),
            'currency_list'     => Currency::getCurrenciesList(),
            'preferences'       => $this->preferences
        ])->with(
            compact('jobs', 'aggregations', 'pagination', 'subscribes', 'count', 'selected')
        );
    }

    /**
     * @param Request $request
     * @param string[] $keys
     * @return bool
     */
    protected function notEmpty(Request $request, array $keys)
    {
        foreach ($keys as $key) {
            if ($request->has($key)) {
                return true;
            }
        }

        return false;
    }
}
