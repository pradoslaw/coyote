<?php
namespace Coyote\Http\Controllers\Job;

use Coyote\Repositories\Eloquent\JobRepository;
use Coyote\Services\Elasticsearch\Builders\Job\AdBuilder;
use Coyote\Services\Elasticsearch\Raw;
use Coyote\Services\Skills\Predictions;
use Coyote\Tag;
use Illuminate\Database\Eloquent;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AdController extends Controller
{
    public function __construct(private JobRepository $job)
    {
        $this->middleware('geocode');
    }

    public function index(Request $request, Predictions $predictions): string
    {
        $builder = new AdBuilder($request);
        $builder->boostLocation($request->attributes->get('geocode'));

        $majorTag = $this->getMajorTag($predictions->getTags());
        if ($majorTag->exists) {
            $builder->boostTags(Raw::escape($majorTag->name));
        }

        $result = $this->job->search($builder);
        if (!$result->total()) {
            return false;
        }

        return view('job.ad', [
            'jobs'         => $result->getSource(),
            'selectedTags' => [$majorTag->name],
            'major_tag'    => $majorTag,
        ]);
    }

    private function getMajorTag(?Eloquent\Collection $tags): Tag
    {
        if (empty($tags) || !count($tags)) {
            return new Tag();
        }
        return $tags->random();
    }
}
