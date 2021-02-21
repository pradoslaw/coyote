<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Repositories\Contracts\TagRepositoryInterface as TagRepository;
use Coyote\Services\Elasticsearch\Builders\Job\AdBuilder;
use Coyote\Services\Elasticsearch\Raw;
use Coyote\Services\Skills\Predictions;

class AdController extends Controller
{
    /**
     * @var JobRepository
     */
    private $job;

    /**
     * @var TagRepository
     */
    private $tag;

    /**
     * @param JobRepository $job
     * @param TagRepository $tag
     */
    public function __construct(JobRepository $job, TagRepository $tag)
    {
        debugbar()->disable();
        parent::__construct();

        $this->job = $job;
        $this->tag = $tag;

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
        $majorTag = $this->getMajorTag($tags);

        if (!empty($majorTag)) {
            $builder->boostTags([sprintf('%s^%.1F', Raw::escape($majorTag->name), 1)]);
        }

        $result = $this->job->search($builder);
        if (!$result->total()) {
            return '';
        }

        // search jobs that might be interesting for user
        return (string) view(
            'job.ad',
            $data,
            ['jobs' => $result->getSource(), 'inverse_tags' => [$majorTag->name], 'major_tag' => $majorTag]
        );
    }

    /**
     * @param \Coyote\Tag[] $tags
     * @return array|\Coyote\Tag
     */
    private function getMajorTag($tags)
    {
        if (empty($tags)) {
            return [];
        }

        return $tags->random();
    }
}
