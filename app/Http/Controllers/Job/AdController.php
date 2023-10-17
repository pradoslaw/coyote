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
use Illuminate\View\View;

class AdController extends Controller
{
    public function __construct(private JobRepository $job)
    {
        $this->middleware('geocode');
    }

    public function index(Request $request, Predictions $predictions): View|string
    {
        $builder = new AdBuilder($request);
        $builder->boostLocation($request->attributes->get('geocode'));
        $majorTag = $this->majorTag($predictions->getTags());
        if ($majorTag->exists) {
            $builder->boostTags(Raw::escape($majorTag->name));
        }
        $result = $this->job->search($builder);
        if (!$result->total()) {
            return '<!-- no recommendations -->';
        }
        return view('job.ad', [], [
            'jobs'         => $result->getSource(),
            'inverse_tags' => [$majorTag->name],
            'major_tag'    => $majorTag
        ]);
    }

    private function majorTag(?Eloquent\Collection $tags): Tag
    {
        if (empty($tags) || count($tags) === 0) {
            return new Tag();
        }
        return $tags->random();
    }
}
