<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;

class TagController extends Controller
{
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
