<?php

namespace Coyote\Http\Controllers\Job;

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
     * HomeController constructor.
     * @param JobRepositoryInterface $job
     */
    public function __construct(JobRepositoryInterface $job)
    {
        parent::__construct();

        $this->job = $job;
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
        return $this->load($request, [
            'terms' => [
                'locations' => [
                    strtolower($name)
                ]
            ]
        ]);
    }

    /**
     * @param $name
     */
    public function tag($name)
    {
        //
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function remote(Request $request)
    {
        return $this->load($request, ['term' => ['is_remote' => 1]]);
    }

    /**
     * @param Request $request
     * @param array $filter
     * @return $this
     */
    private function load(Request $request, $filter = [])
    {
        $query = [];

        if ($request->has('q')) {
            $query = [
                'query_string' => [
                    'query' => $request->get('q'),
                    'fields' => ['title', 'description', 'requirements', 'recruitment', 'tags']
                ]
            ];
        }

        $body = [
            'query' => [
                'filtered' => [
                    'query' => $query,
                    'filter' =>
                        $filter

                ]
            ],

            'sort' => [
                [
                    $request->get('sort', '_score') => $request->get('order', 'desc')
                ]
            ]
        ];

        start_measure('search', 'Elasticsearch');

        // keep in mind that we return data by calling getSource(). This is important because
        // we want to pass collection to the twig (not raw php array)
        $jobs = $this->job->search($body)->getSource();
        stop_measure('search');

        return $this->view('job.home', [
            'ratesList'         => Job::getRatesList(),
            'employmentList'    => Job::getEmploymentList()
        ])->with(
            compact('jobs')
        );
    }
}
