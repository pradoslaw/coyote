<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Repositories\Contracts\TagRepositoryInterface as TagRepository;
use Coyote\Services\Elasticsearch\Builders\Job\AdBuilder;
use Coyote\Services\Elasticsearch\Raw;
use Coyote\Services\Skills\Predictions;
use Coyote\Tag;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

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
//        debugbar()->disable();
//        parent::__construct();

        $this->job = $job;
        $this->tag = $tag;

        $this->middleware('geocode');
    }

    /**
     * @param Predictions $predictions
     * @return string
     */
    public function index(Request $request, Predictions $predictions)
    {
        $builder = new AdBuilder($request);
        $builder->boostLocation($request->attributes->get('geocode'));

        $data = [];
        $tags = $predictions->getTags();
        $majorTag = $this->getMajorTag($tags);

        if ($majorTag->exists) {
            $builder->boostTags(Raw::escape($majorTag->name));
        }

        $result = $this->job->search($builder);
        if (!$result->total()) {
            return false;
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
     * @return \Coyote\Tag
     */
    private function getMajorTag($tags)
    {
        if (empty($tags) || !count($tags)) {
            return new Tag();
        }

        return $tags->random();
    }
}
