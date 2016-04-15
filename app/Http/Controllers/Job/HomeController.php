<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Elasticsearch\Aggs;
use Coyote\Elasticsearch\Filters;
use Coyote\Elasticsearch\Query;
use Coyote\Elasticsearch\QueryBuilderInterface;
use Coyote\Elasticsearch\Sort;
use Coyote\Http\Controllers\Controller;
use Coyote\Parser\Reference\City;
use Coyote\Repositories\Contracts\JobRepositoryInterface;
use Coyote\Repositories\Criteria\Job\PriorDeadline;
use Illuminate\Http\Request;
use Coyote\Job;
use Coyote\Currency;
use Illuminate\Pagination\LengthAwarePaginator;

class HomeController extends Controller
{
    const PER_PAGE = 15;
    const TAB_ALL = 'all';
    const TAB_FILTERED = 'filtered';

    /**
     * @var JobRepositoryInterface
     */
    private $job;

    /**
     * @var QueryBuilderInterface
     */
    private $elasticsearch;

    /**
     * @var Filters\Job\City
     */
    private $city;

    /**
     * @var Filters\Job\Tag
     */
    private $tag;

    /**
     * @var mixed
     */
    private $nav;

    /**
     * @var string
     */
    private $tab = self::TAB_ALL;

    /**
     * @var array|mixed
     */
    private $preferences = [];

    /**
     * HomeController constructor.
     * @param JobRepositoryInterface $job
     * @param QueryBuilderInterface $queryBuilder
     */
    public function __construct(JobRepositoryInterface $job, QueryBuilderInterface $queryBuilder)
    {
        parent::__construct();

        $this->job = $job;
        $this->elasticsearch = $queryBuilder;
        $this->city = new Filters\Job\City();
        $this->tag = new Filters\Job\Tag();

        $this->public['validateUrl'] = route('job.tag.validate');
        $this->public['promptUrl'] = route('job.tag.prompt');

        $this->nav = $this->getMenu();
    }

    /**
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $this->preferences = json_decode($this->getSetting('job.preferences', '{}'));

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
        if ($this->inputNotEmpty($request, ['q', 'city', 'remote', 'tag'])) {
            $this->tab = self::TAB_ALL;
        }

        if ($this->tab == self::TAB_FILTERED) {
            $this->applyPreferences();
        }

        return $this->load($request);
    }

    /**
     * @param Request $request
     * @param $name
     * @return $this
     */
    public function city(Request $request, $name)
    {
        $this->city->addCity($name);

        return $this->load($request);
    }

    /**
     * @param Request $request
     * @param $name
     * @return HomeController
     */
    public function tag(Request $request, $name)
    {
        $this->tag->addTag($name);

        return $this->load($request);
    }

    /**
     * @param Request $request
     * @param $name
     * @return HomeController
     */
    public function firm(Request $request, $name)
    {
        $this->elasticsearch->addFilter(new Filters\Job\Firm($name));

        return $this->load($request);
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function remote(Request $request)
    {
        $this->applyRemoteFilter();

        return $this->load($request);
    }

    /**
     * @param Request $request
     * @return $this
     */
    private function load(Request $request)
    {
        $this->nav->get($this->tab)->active();

        if ($request->has('q')) {
            $this->elasticsearch->addQuery(
                new Query($request->get('q'), ['title', 'description', 'requirements', 'recruitment', 'tags'])
            );
        }

        if ($request->has('city')) {
            $this->city->addCity($request->get('city'));
        }

        if ($request->has('tag')) {
            $this->tag->addTag($request->get('tag'));
        }

        if ($request->has('salary')) {
            $this->applySalaryFilter($request->get('salary'), $request->get('currency'));
        }

        if ($request->has('remote')) {
            $this->applyRemoteFilter();
        }

        $this->elasticsearch->addSort(
            new Sort($request->get('sort', '_score'), $request->get('order', 'desc'))
        );
        $this->elasticsearch->addSort(new Sort('_id', 'desc'));

        // it's really important. we MUST show only active offers
        $this->elasticsearch->addFilter(new Filters\Range('deadline_at', ['gte' => 'now']));
        $this->elasticsearch->addFilter($this->city);
        $this->elasticsearch->addFilter($this->tag);

        // facet search
        $this->elasticsearch->addAggs(new Aggs\Job\Location());
        $this->elasticsearch->addAggs(new Aggs\Job\Remote());
        $this->elasticsearch->addAggs(new Aggs\Job\Tag());
        $this->elasticsearch->setSize(self::PER_PAGE * ($request->get('page', 1) - 1), self::PER_PAGE);

        start_measure('search', 'Elasticsearch');

        $build = $this->elasticsearch->build();
        // show build query in laravel's debugbar
        debugbar()->debug($build);

        $response = $this->job->search($build);
        stop_measure('search');

        // keep in mind that we return data by calling getSource(). This is important because
        // we want to pass collection to the twig (not raw php array)
        $jobs = $response->getSource();

        $context = !$request->has('q') ? 'global.' : '';
        $aggregations = [
            'cities' => $response->getAggregations("${context}locations.city_original"),
            'tags' => $response->getAggregations("${context}tags"),
            'remote' => $response->getAggregations("${context}remote")
        ];

        $pagination = new LengthAwarePaginator(
            $jobs, $response->totalHits(), self::PER_PAGE, LengthAwarePaginator::resolveCurrentPage(), [
                'path' => LengthAwarePaginator::resolveCurrentPath()
            ]
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
                'tags'          => $this->tag->getTags(),
                'cities'        => array_map('mb_strtolower', $this->city->getCities()),
                'remote'        => $request->has('remote') || $this->getRouter()->currentRouteName() === 'job.remote'
            ];
        }

        return $this->view('job.home', [
            'ratesList'         => Job::getRatesList(),
            'employmentList'    => Job::getEmploymentList(),
            'currencyList'      => Currency::lists('name', 'id'),
            'preferences'       => $this->preferences,
            'nav'               => $this->nav
        ])->with(
            compact('jobs', 'aggregations', 'pagination', 'subscribes', 'count', 'selected')
        );
    }

    /**
     * @return mixed
     */
    protected function getMenu()
    {
        return app('menu')->make('job.home', function ($menu) {
            $html = app('html')->tag('i', '', ['id' => 'btn-editor', 'class' => 'fa fa-cog']);

            $menu->add('Wszystkie', ['route' => ['job.home', 'tab' => 'all'], 'nickname' => 'all']);
            $menu->add('Wybrane dla mnie', [
                'route' => ['job.home', 'tab' => 'filtered'], 'nickname' => 'filtered'
            ])->append($html);
        });
    }

    /**
     * @todo Nie mam pomyslu w tej chwili, aby to lepiej rozpisac... te if'y nie wygladaja zbyt dobrze
     */
    protected function applyPreferences()
    {
        if (!empty($this->preferences->city)) {
            $this->city->setCities((new City())->grab($this->preferences->city));
        }

        if (!empty($this->preferences->tags)) {
            $this->tag->setTags($this->preferences->tags);
        }

        if (!empty($this->preferences->is_remote)) {
            $this->applyRemoteFilter();
        }

        if (!empty($this->preferences->salary)) {
            $this->applySalaryFilter($this->preferences->salary, $this->preferences->currency_id);
        }
    }

    /**
     * @param Request $request
     * @param array $keys
     * @return bool
     */
    protected function inputNotEmpty(Request $request, array $keys)
    {
        foreach ($keys as $key) {
            if ($request->has($key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Apply remote job filter
     */
    protected function applyRemoteFilter()
    {
        $this->elasticsearch->addFilter(new Filters\Job\Remote());
    }

    /**
     * @param $salary
     * @param $currencyId
     */
    protected function applySalaryFilter($salary, $currencyId)
    {
        $this->elasticsearch->addFilter(new Filters\Range('salary', ['gte' => $salary]));
        $this->elasticsearch->addFilter(new Filters\Job\Currency($currencyId));
    }
}
