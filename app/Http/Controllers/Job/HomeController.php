<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Elasticsearch\Aggs\Job\Location;
use Coyote\Elasticsearch\Filters\Job\City;
use Coyote\Elasticsearch\Filters\Job\Firm;
use Coyote\Elasticsearch\Filters\Job\Tags;
use Coyote\Elasticsearch\Job\Remote;
use Coyote\Elasticsearch\Query;
use Coyote\Elasticsearch\QueryBuilderInterface;
use Coyote\Elasticsearch\Sort;
use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\JobRepositoryInterface;
use Illuminate\Http\Request;
use Coyote\Job;
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
     * @var City
     */
    private $city;

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
        $this->city = new City();
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
        $this->elasticsearch->addFilter(new Tags($name));

        return $this->load($request);
    }

    /**
     * @param Request $request
     * @param $name
     * @return HomeController
     */
    public function firm(Request $request, $name)
    {
        $this->elasticsearch->addFilter(new Firm($name));

        return $this->load($request);
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function remote(Request $request)
    {
        $this->elasticsearch->addFilter(new Remote());

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

        $this->elasticsearch->addSort(
            new Sort($request->get('sort', '_score'), $request->get('order', 'desc'))
        );

        $this->elasticsearch->addFilter($this->city);

        // facet search
        $this->elasticsearch->addAggs(new Location());
        $this->elasticsearch->setSize($request->get('page'), self::PER_PAGE);

        start_measure('search', 'Elasticsearch');

        $response = $this->job->search($this->elasticsearch->build());
        stop_measure('search');

        // keep in mind that we return data by calling getSource(). This is important because
        // we want to pass collection to the twig (not raw php array)
        $jobs = $response->getSource();
        $aggregations = [
            'city' => $response->getAggregations('global.locations.city')
        ];

        $pagination = new LengthAwarePaginator(
            $jobs, $response->totalHits(), self::PER_PAGE, LengthAwarePaginator::resolveCurrentPage(), [
                'path' => LengthAwarePaginator::resolveCurrentPath()
            ]
        );

        $pagination->appends($request->except('page'));

        return $this->view('job.home', [
            'ratesList'         => Job::getRatesList(),
            'employmentList'    => Job::getEmploymentList(),
            'cities'            => array_map('mb_strtolower', $this->city->getCities())
        ])->with(
            compact('jobs', 'aggregations', 'pagination')
        );
    }
}
