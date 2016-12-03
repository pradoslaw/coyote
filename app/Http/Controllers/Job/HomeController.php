<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Job\Preferences;
use Coyote\Services\Elasticsearch\Builders\Job\SearchBuilder;
use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Repositories\Criteria\Job\PriorDeadline;
use Illuminate\Http\Request;
use Coyote\Job;
use Coyote\Currency;
use Illuminate\Pagination\LengthAwarePaginator;
use Lavary\Menu\Builder;
use Lavary\Menu\Menu;

class HomeController extends Controller
{
    const TAB_ALL = 'all';
    const TAB_FILTERED = 'filtered';

    const DEFAULT_TAB = self::TAB_FILTERED;

    /**
     * @var JobRepository
     */
    private $job;

    /**
     * @var string
     */
    private $tab = self::TAB_ALL;

    /**
     * @var array|mixed
     */
    private $preferences = [];

    /**
     * @var SearchBuilder
     */
    private $builder;

    /**
     * @param JobRepository $job
     * @param Request $request
     */
    public function __construct(JobRepository $job, Request $request)
    {
        parent::__construct();

        $this->job = $job;
        $this->builder = new SearchBuilder($request);

        $this->public['promptUrl'] = route('job.tag.prompt');
    }

    /**
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $this->preferences = new Preferences($this->getSetting('job.preferences'));

        $this->tab = $request->get('tab', $this->getSetting('job.tab', self::TAB_FILTERED));
        $validator = $this->getValidationFactory()->make(
            $request->all(),
            ['tab' => 'sometimes|in:' . self::TAB_ALL . ',' . self::TAB_FILTERED]
        );

        if ($validator->fails()) {
            $this->tab = self::TAB_FILTERED;
        }

        if ($request->has('tab')) {
            $this->setSetting('job.tab', $this->tab);
        }

        // if user want to filter job offers, we MUST select "all" tab
        if ($this->notEmpty($request, ['q', 'city', 'remote', 'tag'])) {
            $this->tab = self::TAB_ALL;
        }

        if ($this->tab == self::TAB_FILTERED) {
            $this->builder->setPreferences($this->preferences);
        }

        return $this->load($request);
    }

    /**
     * @param Request $request
     * @param $name
     * @return \Illuminate\View\View
     */
    public function city(Request $request, $name)
    {
        $this->builder->city->addCity($name);

        return $this->load($request);
    }

    /**
     * @param Request $request
     * @param $name
     * @return \Illuminate\View\View
     */
    public function tag(Request $request, $name)
    {
        $this->builder->tag->addTag($name);

        return $this->load($request);
    }

    /**
     * @param Request $request
     * @param $name
     * @return \Illuminate\View\View
     */
    public function firm(Request $request, $name)
    {
        $this->builder->addFirmFilter($name);

        return $this->load($request);
    }

    /**
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function remote(Request $request)
    {
        $this->builder->addRemoteFilter();

        return $this->load($request);
    }

    /**
     * @param Request $request
     * @return \Illuminate\View\View
     */
    private function load(Request $request)
    {
        start_measure('search', 'Elasticsearch');

        $build = $this->builder->build()->build();
        // show build query in laravel's debugbar
        debugbar()->debug(json_encode($build));

        $result = $this->job->search($build);
        stop_measure('search');

        // keep in mind that we return data by calling getSource(). This is important because
        // we want to pass collection to the twig (not raw php array)
        $jobs = $result->getSource();

        $context = !$request->has('q') ? 'global.' : '';
        $aggregations = [
            'cities'        => $result->getAggregations("${context}locations.city_original"),
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

        $pagination->appends($request->except('page'));

        // we need to display actual number of active offers so don't remove line below!
        $this->job->pushCriteria(new PriorDeadline());
        $count = $this->job->count();

        $subscribes = [];

        if ($this->userId) {
            $subscribes = $this->job->subscribes($this->userId);
        }

        $selected = [];
        if ($this->tab !== self::TAB_FILTERED) {
            $selected = [
                'tags'          => $this->builder->tag->getTags(),
                'cities'        => array_map('mb_strtolower', $this->builder->city->getCities()),
                'remote'        => $request->has('remote') || $this->getRouter()->currentRouteName() === 'job.remote'
            ];
        }

        return $this->view('job.home', [
            'ratesList'         => Job::getRatesList(),
            'employmentList'    => Job::getEmploymentList(),
            'currencyList'      => Currency::lists('name', 'id'),
            'preferences'       => $this->preferences,
            'tabs'              => $this->getTabs()
        ])->with(
            compact('jobs', 'aggregations', 'pagination', 'subscribes', 'count', 'selected')
        );
    }

    /**
     * @return \Lavary\Menu\Builder
     */
    protected function getTabs()
    {
        return app(Menu::class)->make('job.home', function (Builder $builder) {
            $icon = app('html')->tag('i', '', ['id' => 'btn-editor', 'class' => 'fa fa-cog', 'title' => 'Ustaw swoje preferencje']);

            $builder->add('Wszystkie', ['route' => ['job.home', 'tab' => 'all'], 'nickname' => 'all']);
            $builder->add('Wybrane dla mnie', ['route' => ['job.home', 'tab' => 'filtered'], 'nickname' => 'filtered']);

            $builder->get('filtered')->append($icon);
            $builder->get($this->tab)->active();
        });
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
