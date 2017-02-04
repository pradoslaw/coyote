<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Job\Preferences;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Repositories\Contracts\PageRepositoryInterface as PageRepository;
use Coyote\Repositories\Criteria\Job\PriorDeadline;
use Coyote\Services\Elasticsearch\Builders\Job\AdBuilder;

class AdController extends Controller
{
    /**
     * @var JobRepository
     */
    private $job;

    /**
     * @var PageRepository
     */
    private $page;

    /**
     * @param JobRepository $job
     * @param PageRepository $page
     */
    public function __construct(JobRepository $job, PageRepository $page)
    {
        debugbar()->disable();
        parent::__construct();

        $this->job = $job;
        $this->page = $page;

        $this->middleware('geocode');
    }

    /**
     * @return string
     */
    public function index()
    {
        $builder = new AdBuilder($this->request);
        $builder->boostLocation($this->request->attributes->get('geocode'));

        $data = [];
        $tags = $this->getRefererTags();

        if ($tags) {
            $builder->boostTags($tags);
        } else {
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
        }

        $result = $this->job->search($builder);
        if (!$result->total()) {
            return '';
        }

        // search jobs that might be close to your location
        return (string) view('job.ad', $data, ['jobs' => $result->getSource()]);
    }

    /**
     * @return array
     */
    private function getRefererTags()
    {
        $referer = filter_var($this->request->headers->get('referer'), FILTER_SANITIZE_URL);
        if (!$referer) {
            return [];
        }

        $path = parse_url($referer, PHP_URL_PATH);
        $page = $this->page->findByPath($path);

        if (!$page) {
            return [];
        }

        return $page->tags;
    }
}
