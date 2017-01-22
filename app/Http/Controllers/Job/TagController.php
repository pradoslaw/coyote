<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Repositories\Criteria\Job\PriorDeadline;
use Coyote\Repositories\Contracts\TagRepositoryInterface as TagRepository;

class TagController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function submit()
    {
        $this->validate($this->request, ['name' => 'required|string|max:25|tag']);

        return view('job.submit.partials.tag', [
            'tag' => [
                'name' => $this->request->input('name'),
                'priority' => 1
            ]
        ]);
    }

    /**
     * @param TagRepository $tag
     * @param JobRepository $job
     * @return \Illuminate\View\View
     */
    public function prompt(TagRepository $tag, JobRepository $job)
    {
        // we don't wanna tags with "#" at the beginning
        $this->request->merge(['q' => ltrim($this->request->input('q'), '#')]);

        $this->validate($this->request, ['q' => 'required|string|max:25']);
        $tags = $tag->lookupName($this->request->input('q'));

        $job->pushCriteria(new PriorDeadline());
        $tags = $job->getTagsWeight($tags->pluck('id')->toArray());

        return view('components.tags')->with('tags', $tags);
    }

    /**
     * Validate tag
     */
    public function valid()
    {
        // we don't wanna tags with "#" at the beginning
        $this->request->merge(['t' => ltrim($this->request->input('t'), '#')]);

        $this->validate($this->request, ['t' => 'required|string|max:25|tag']);
    }

    /**
     * @param JobRepository $job
     * @return \Illuminate\Http\JsonResponse
     */
    public function suggestions(JobRepository $job)
    {
        $suggestions = $job->getTagSuggestions($this->request->input('t'));

        return response()->json($suggestions);
    }
}
