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
     * @param TagRepository $tag
     * @param JobRepository $job
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(TagRepository $tag, JobRepository $job)
    {
        $tags = $tag->findAllBy('category_id', Tag\Category::LANGUAGE);
        $result = $hashTags = $count = [];

        foreach ($tags as $tag) {
            $builder = new FbBuilder();
            $builder->setLanguage($tag->name);

            $source = $job->search($builder)->getSource();

            if ($source) {
                $hashTags[] = '#' . $tag->name;

                $result[$tag->real_name] = $source;
                $count[$tag->real_name] = count($result[$tag->real_name]);
            }
        }

        array_multisort($count, SORT_DESC, $result);

        return view('job.fb', ['hash_tags' => $hashTags, 'result' => $result]);
    }
}
