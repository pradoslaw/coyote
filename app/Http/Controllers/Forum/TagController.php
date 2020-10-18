<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Resources\TagResource;
use Illuminate\Http\Request;
use Coyote\Repositories\Contracts\TagRepositoryInterface as TagRepository;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;

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

    /**
     * @param Request $request
     * @param TagRepository $tag
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     * @throws \Illuminate\Validation\ValidationException
     */
    public function prompt(Request $request, TagRepository $tag)
    {
        // we don't wanna tags with "#" at the beginning
        $request->merge(['q' => ltrim($request['q'], '#')]);

        $this->validate($request, ['q' => 'required|string|max:25']);

        // search for tag
        $tags = $tag->lookupName($request->input('q'));

        TagResource::withoutWrapping();

        return TagResource::collection($tags);
    }

    /**
     * @param Request $request
     */
    public function valid(Request $request)
    {
        // we don't wanna tags with "#" at the beginning
        $request->merge(['t' => ltrim($request['t'], '#')]);

        $this->validate($request, ['t' => 'required|string|max:25|tag|tag_creation:50']);
    }
}
