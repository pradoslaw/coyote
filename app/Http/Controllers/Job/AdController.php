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

        if (!empty($tags)) {
            $builder->boostTags($this->boost($tags));
        }

        $result = $this->job->search($builder);
        if (!$result->total()) {
            return '';
        }

        $majorTag = $this->getMajorTag($tags);

        // search jobs that might be interesting for user
        return (string) view(
            'job.ad',
            $data,
            ['jobs' => $result->getSource(), 'inverse_tags' => $this->getTagsNames($majorTag), 'major_tag' => $majorTag]
        );
    }

    /**
     * @param \Coyote\Tag[] $tags
     * @return array
     */
    private function boost($tags)
    {
        $result = [];

        foreach ($tags as $tag) {
            if (!empty($tag)) {
                $result[] = sprintf('%s^%.1F', Raw::escape($tag->name), 1);
            }
        }

        return $result;
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

        return $tags->first();
    }

    /**
     * @param \Coyote\Tag[] $tags
     * @return array
     */
    private function getTagsNames($tags)
    {
        if (empty($tags)) {
            return [];
        }

        return $tags->pluck('name')->toArray();
    }
}
