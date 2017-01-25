<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Job\Preferences;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Repositories\Criteria\Job\PriorDeadline;
use Coyote\Services\Elasticsearch\Builders\Job\AdBuilder;

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

        $this->middleware('geocode');
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
        $builder->setBoostLocation($this->request->attributes->get('geocode'));

        $preferences = new Preferences($this->getSetting('job.preferences'));

        $builder->setPreferences($preferences);

        if ($preferences->isEmpty()) {
            $location = $this->request->attributes->get('geocode');

            if ($location->isValid()) {
                $this->job->pushCriteria(new PriorDeadline());

                $data = [
                    'location' => $location,
                    'offers_count' => $this->job->countCityOffers($location->city)
                ];
            }
        }

        $result = $this->job->search($builder);
        if (!$result->total()) {
            return '';
        }

        // search jobs that might be close to your location
        return (string) view('job.ad', $data, ['jobs' => $result->getSource()]);
    }
}
