<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Services\Elasticsearch\Builders\Job\AdBuilder;
use Coyote\Services\Skills\Predictions;

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
     * @param Predictions $predictions
     * @return string
     */
    public function index(Predictions $predictions)
    {
        $builder = new AdBuilder($this->request);
        $builder->boostLocation($this->request->attributes->get('geocode'));

        $data = [];
        $tags = $predictions->getTags();

        if (!empty($tags)) {
            $builder->boostTags($tags);
        }

        $result = $this->job->search($builder);
        if (!$result->total()) {
            return '';
        }

        // search jobs that might be interesting for user
        return (string) view('job.ad', $data, ['jobs' => $result->getSource(), 'tags' => $tags]);
    }
}
