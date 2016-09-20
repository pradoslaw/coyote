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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $output = $this->getCacheFactory()->remember('ad:' . $request->ip(), 60, function () {
            return $this->load($this->lookupLocation());
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
        if (!$result->total()) {
            return '';
        }

        // search jobs that might be close to your location
        return (string) view('job.ad', ['jobs' => $result->getSource(), 'location' => $location]);
    }

    /**
     * @return array|mixed
     */
    private function lookupLocation()
    {
        if (auth()->check() && auth()->user()->location) {
            // get by city if available...
            $result = $this->getByCity(auth()->user()->location);

            // only first result please...
            if ($result) {
                return $result[0];
            }
        }

        // ... otherwise lookup by ip
        return $this->getByIp(request()->ip());
    }

    /**
     * @param string $ip
     * @return array
     */
    private function getByIp($ip)
    {
        return $this->getGeoIp()->ip($ip);
    }

    /**
     * @param string $city
     * @return array
     */
    private function getByCity($city)
    {
        return $this->getGeoIp()->city($city);
    }

    /**
     * @return \Coyote\Services\GeoIp\GeoIp
     */
    private function getGeoIp()
    {
        return app('geo-ip');
    }
}
