<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Repositories\Contracts\TagRepositoryInterface as TagRepository;
use Coyote\Reputation;
use Illuminate\Http\Request;

class TagController extends BaseController
{
    /**
     * @param Request $request
     * @return array|null
     * @throws \Illuminate\Validation\ValidationException
     */
    public function save(Request $request)
    {
        $this->validate($request, ['tags' => 'array', 'tags.*' => 'required|max:25|tag']);

        $tags = json_encode($request->get('tags', []));
        $this->setSetting('forum.tags', $tags);

        return $this->getUserTags();
    }

    public function validation(Request $request, TagRepository $repository)
    {
        $this->validate($request, [
            'tags'   => 'array',
            'tags.*' => 'string',
        ]);

        if ($this->auth->reputation < Reputation::CREATE_TAGS) {
            return response(['warning' => false]);
        }

        $invalid = [];

        foreach ($request->input('tags') as $tag) {
            if (!$repository->exists($tag)) {
                $invalid[] = $tag;
            }
        }

        return response([
            'warning' => count($invalid) > 0,
            'message' => trans('validation.tag_not_exist', ['value' => implode(', ', $invalid)]),
        ]);
    }
}
