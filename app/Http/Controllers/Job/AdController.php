<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Services\Elasticsearch\Geodistance;
use Illuminate\Http\Request;
use Coyote\Services\Elasticsearch\QueryBuilder;

class AdController extends Controller
{
    /**
     * @var JobRepository
     */
    private $job;

    /**
     * @param JobRepository $job
     */
    public function __construct(JobRepository $job)
    {
        debugbar()->disable();
        parent::__construct();

        $this->job = $job;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $location = $this->getLocation($request->ip());

        $builder = new QueryBuilder();
        $builder->setSize(0, 4);
        $builder->addSort(new Geodistance($location['latitude'], $location['longitude']));

        // search jobs that might be close to your location
        return view('job.ad', ['jobs' => $this->job->search($builder->build()), 'location' => $location]);
    }

    /**
     * @param string $ip
     * @return array
     */
    protected function getLocation($ip)
    {
        return $this->getCacheFactory()->remember('ip:' . $ip, 60, function () use ($ip) {
            return app('geo-ip')->ip($ip);
        });
    }
}
