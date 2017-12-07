<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Repositories\Contracts\TagRepositoryInterface as TagRepository;
use Coyote\Services\Elasticsearch\Builders\Job\FbBuilder;
use Coyote\Tag;

class FbController extends Controller
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
     * @param TagRepository $tag
     * @param JobRepository $job
     */
    public function __construct(TagRepository $tag, JobRepository $job)
    {
        parent::__construct();

        $this->tag = $tag;
        $this->job = $job;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showByCategory()
    {
        $tags = $this->tag->findAllBy('category_id', Tag\Category::LANGUAGE);
        $result = $hashTags = $count = [];

        foreach ($tags as $tag) {
            $builder = new FbBuilder();
            $builder->setLanguage($tag->name);
            $builder->onlyFromLastWeek();

            $source = $this->job->search($builder)->getSource();

            if ($source) {
                $hashTags[] = '#' . $tag->name;

                $result[$tag->real_name] = $source;
                $count[$tag->real_name] = count($result[$tag->real_name]);
            }
        }

        array_multisort($count, SORT_DESC, $result);

        return view('job.fb_category', ['hash_tags' => $hashTags, 'result' => $result]);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showByKeyword()
    {
        $builder = new FbBuilder();
        $builder->setLanguage($this->request->input('q'));

        $source = $this->job->search($builder)->getSource();
        $result = [];

        foreach ($source as $job) {
            foreach ($job->get('locations') as $location) {
                $result[$location->get('city')][] = $job;
            }
        }

        return view('job.fb_keyword', ['result' => $result]);
    }
}
