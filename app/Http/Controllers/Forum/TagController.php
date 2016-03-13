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
        $rules = [];

        if (is_array($request->get('tags'))) {
            // @todo w laravel 5.2. sposob walidacji mozna rozwiazac inaczej
            foreach ($request->get('tags') as $key => $val) {
                $rules['tags.' . $key] = 'required|max:25|tag';
            }
        }

        $this->validate($request, $rules);

        $tags = json_encode($request->get('tags', []));
        $this->setSetting('forum.tags', $tags);

        return view('forum.partials.tags')->with('tags', $this->getUserTags());
    }
}
