<?php

namespace Coyote\Http\Controllers\Job;

use Carbon\Carbon;
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
     * @return string
     */
    public function index(Request $request)
    {
        $output = $this->getCacheFactory()->remember('ad:' . $request->ip(), 60, function () use ($request) {
            return $this->load($this->getLocation($request->ip()));
        });

        // cache output response for 1h
        return response($output)->setMaxAge(3600)->setExpires(new Carbon('+1 hour'));
    }

    /**
     * @param array $location
     * @return string
     */
    private function load($location)
    {
        $builder = new QueryBuilder();
        $builder->setSize(0, 4);
        $builder->addSort(new Geodistance($location['latitude'], $location['longitude']));

        $result = $this->job->search($builder->build());
        if (!$result->totalHits()) {
            return '';
        }

        // search jobs that might be close to your location
        return (string) view('job.ad', ['jobs' => $result->getSource(), 'location' => $location]);
    }

    /**
     * @param string $ip
     * @return array
     */
    private function getLocation($ip)
    {
        return app('geo-ip')->ip($ip);
    }
}
