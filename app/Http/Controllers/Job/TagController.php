<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\JobRepositoryInterface as Job;
use Coyote\Repositories\Criteria\Job\PriorDeadline;
use Illuminate\Http\Request;
use Coyote\Repositories\Contracts\TagRepositoryInterface as Tag;

class TagController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function submit(Request $request)
    {
        $this->validate($request, ['name' => 'required|string|max:25|tag']);

        return view('job.submit.partials.tag', [
            'tag' => [
                'name' => $request->name,
                'priority' => 1
            ]
        ]);
    }

    /**
     * @param Request $request
     * @param Tag $tag
     * @param Job $job
     * @return $this
     */
    public function prompt(Request $request, Tag $tag, Job $job)
    {
        $this->validate($request, ['q' => 'required|string|max:25']);
        $tags = $tag->lookupName(ltrim($request['q'], '#'));

        $job->pushCriteria(new PriorDeadline());
        $tags = $job->getTagsWeight($tags->pluck('id')->toArray());

        return view('components.tags')->with('tags', $tags);
    }

    /**
     * @param Request $request
     */
    public function valid(Request $request)
    {
        $this->validate($request, ['t' => 'required|string|max:25|tag']);
    }
}