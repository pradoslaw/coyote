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

class HomeController extends Controller
{
    /**
     * @var JobRepositoryInterface
     */
    private $job;

    /**
     * @var QueryBuilderInterface
     */
    private $elasticsearch;

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
        $this->elasticsearch->addFilter(new City($name));

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

        // facet search
        $this->elasticsearch->addAggs(new Location());

        start_measure('search', 'Elasticsearch');

        $response = $this->job->search($this->elasticsearch->build());
        stop_measure('search');

        // keep in mind that we return data by calling getSource(). This is important because
        // we want to pass collection to the twig (not raw php array)
        $jobs = $response->getSource();
        $aggregations = [
            'city' => $response->getAggregations('locations.city')
        ];

        return $this->view('job.home', [
            'ratesList'         => Job::getRatesList(),
            'employmentList'    => Job::getEmploymentList()
        ])->with(
            compact('jobs', 'aggregations')
        );
    }
}
