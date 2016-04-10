<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
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
     * @return $this
     */
    public function prompt(Request $request, Tag $tag)
    {
        $this->validate($request, ['q' => 'required|string|max:25']);
        return view('components.tags')->with('tags', $tag->lookupName(ltrim($request['q'], '#')));
    }

    /**
     * @param Request $request
     */
    public function valid(Request $request)
    {
        $this->validate($request, ['t' => 'required|string|max:25|tag']);
    }
}