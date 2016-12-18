<?php

namespace Coyote\Http\Controllers\Job;

use Carbon\Carbon;
use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Services\Elasticsearch\Geodistance;
use Coyote\Services\Geocoder\Location;
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
        $output = $this->getCacheFactory()->remember('ad:' . md5($request->session()->getId()), 60, function () use ($request) {
            return $this->load($this->lookupLocation($request));
        });

        // cache output response for 1h
        return response($output)->setMaxAge(3600)->setExpires(new Carbon('+1 hour'));
    }

    /**
     * @param Location $location
     * @return string
     */
    private function load(Location $location)
    {
        $data = [];

        $builder = new QueryBuilder();
        $builder->setSize(0, 4);

        if ($location->longitude !== null || $location->latitude !== null) {
            $builder->addSort(new Geodistance($location->latitude, $location->longitude));

            $data = [
                'location'      => $location,
                'offers_count'  => $this->job->countOffersInCity($location->city)
            ];
        }

        $result = $this->job->search($builder->build());
        if (!$result->total()) {
            return '';
        }

        // search jobs that might be close to your location
        return (string) view('job.ad', $data, ['jobs' => $result->getSource()]);
    }

    /**
     * @param Request $request
     * @return Location
     */
    private function lookupLocation(Request $request)
    {
        if ($this->userId !== null && $this->auth->latitude !== null && $this->auth->longitude !== null) {
            return new Location([
                'latitude' => $this->auth->latitude,
                'longitude' => $this->auth->longitude,
                'city' => $this->auth->location
            ]);
        }

        // ... otherwise lookup by ip
        return new Location($this->getByIp($request->ip()) ?: []);
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
     * @return \Coyote\Services\GeoIp\GeoIp
     */
    private function getGeoIp()
    {
        return app('geo-ip');
    }
}
