<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Elasticsearch\Aggs;
use Coyote\Elasticsearch\Filters;
use Coyote\Elasticsearch\Query;
use Coyote\Elasticsearch\QueryBuilderInterface;
use Coyote\Elasticsearch\Sort;
use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\JobRepositoryInterface;
use Coyote\Repositories\Criteria\Job\PriorDeadline;
use Illuminate\Http\Request;
use Coyote\Job;
use Coyote\Currency;
use Illuminate\Pagination\LengthAwarePaginator;

class HomeController extends Controller
{
    const PER_PAGE = 15;

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
    }

    /**
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
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
        $this->elasticsearch->addFilter(new Filters\Job\Remote());

        return $this->load($request);
    }

    /**
     * @param Request $request
     * @return $this
     */
    private function load(Request $request)
    {
        if ($request->has('q')) {
            $this->elasticsearch->addQuery(
                new Query($request->get('q'), ['title', 'description', 'requirements', 'recruitment', 'tags'])
            );
        }

        if ($request->has('city')) {
            $this->city->addCity($request->get('city'));
        }

        $this->elasticsearch->addSort(
            new Sort($request->get('sort', '_score'), $request->get('order', 'desc'))
        );

        // it's really important. we MUST show only active offers
        $this->elasticsearch->addFilter(new Filters\Range('deadline_at', ['gte' => 'now']));
        $this->elasticsearch->addFilter($this->city);
        $this->elasticsearch->addFilter($this->tag);

        // facet search
        $this->elasticsearch->addAggs(new Aggs\Job\Location());
        $this->elasticsearch->addAggs(new Aggs\Job\Tag());
        $this->elasticsearch->setSize($request->get('page'), self::PER_PAGE);

        start_measure('search', 'Elasticsearch');

        // show build query in laravel's debugbar
        debugbar()->debug($this->elasticsearch->build());

        $response = $this->job->search($this->elasticsearch->build());
        stop_measure('search');

        // keep in mind that we return data by calling getSource(). This is important because
        // we want to pass collection to the twig (not raw php array)
        $jobs = $response->getSource();
        $aggregations = [
            'cities' => $response->getAggregations('global.locations.city'),
            'tags' => $response->getAggregations('global.tags')
        ];

        $pagination = new LengthAwarePaginator(
            $jobs, $response->totalHits(), self::PER_PAGE, LengthAwarePaginator::resolveCurrentPage(), [
                'path' => LengthAwarePaginator::resolveCurrentPath()
            ]
        );

        $pagination->appends($request->except('page'));

        $this->job->pushCriteria(new PriorDeadline());
        $count = $this->job->count();

        return $this->view('job.home', [
            'ratesList'         => Job::getRatesList(),
            'employmentList'    => Job::getEmploymentList(),
            'currencyList'      => Currency::lists('name', 'id'),
            'selected' => [
                'tags'          => $this->tag->getTags(),
                'cities'        => array_map('mb_strtolower', $this->city->getCities())
            ]
        ])->with(
            compact('jobs', 'aggregations', 'pagination', 'count')
        );
    }
}
