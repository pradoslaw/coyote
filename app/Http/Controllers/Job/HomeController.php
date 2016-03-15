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
        $body = [
            'sort' => [
                [
                    $request->get('sort', '_score') => $request->get('order', 'desc')
                ]
            ]
        ];

        if ($request->has('q')) {
            $body['query'] = [
                'query_string' => [
                    'query' => $request->get('q'),
                    'fields' => ['title', 'description', 'requirements', 'recruitment', 'tags']
                ]
            ];
        }

        $jobs = $this->job->search($body);

        return $this->view('job.home', [
            'ratesList'         => Job::getRatesList(),
            'employmentList'    => Job::getEmploymentList()
        ])->with(
            compact('jobs')
        );
    }
}
