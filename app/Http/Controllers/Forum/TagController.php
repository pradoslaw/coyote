<?php

namespace Coyote\Http\Controllers\Forum;

use Illuminate\Http\Request;

class TagController extends BaseController
{
    /**
     * @param Request $request
     * @return $this
     */
    public function save(Request $request)
    {
        $this->validate($request, ['tags.*' => 'required|max:25|tag']);

        $tags = json_encode($request->get('tags', []));
        $this->setSetting('forum.tags', $tags);

        return view('forum.partials.tags')->with('tags', $this->getUserTags());
    }
}
