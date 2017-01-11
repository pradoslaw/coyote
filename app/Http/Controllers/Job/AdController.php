<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Job\Preferences;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Services\Elasticsearch\Builders\Job\AdBuilder;
use Coyote\Services\Elasticsearch\Geodistance;
use Coyote\Services\Geocoder\Location;

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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        return response($this->load());
    }

    /**
     * @return string
     */
    private function load()
    {
        $data = [];

        $builder = new AdBuilder($this->request);
        $preferences = $this->getSetting('job.preferences');

        $builder->setPreferences(new Preferences($preferences));
        $builder->setSessionId($this->sessionId);

        if ($preferences === null) {
            $location = $this->lookupLocation();

            if ($location->isValid()) {
                $builder->setSort(new Geodistance($location->latitude, $location->longitude));

                $data = [
                    'location' => $location,
                    'offers_count' => $this->job->countOffersInCity($location->city)
                ];
            }
        }

        $result = $this->job->search($builder->build()->build());
        if (!$result->total()) {
            return '';
        }

        // search jobs that might be close to your location
        return (string) view('job.ad', $data, ['jobs' => $result->getSource()]);
    }

    /**
     * @return Location
     */
    private function lookupLocation()
    {
        if ($this->userId !== null && $this->auth->latitude !== null && $this->auth->longitude !== null) {
            return new Location([
                'latitude'  => $this->auth->latitude,
                'longitude' => $this->auth->longitude,
                'city'      => $this->auth->location
            ]);
        }

        // ... otherwise lookup by ip
        return new Location($this->getByIp($this->request->ip()) ?: []);
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
