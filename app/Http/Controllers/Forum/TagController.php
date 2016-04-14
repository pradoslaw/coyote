<?php

namespace Coyote\Http\Controllers\Forum;

use Illuminate\Http\Request;
use Coyote\Repositories\Contracts\TagRepositoryInterface as Tag;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;

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

    /**
     * @param Request $request
     * @param Tag $tag
     * @param Forum $forum
     * @return $this
     */
    public function prompt(Request $request, Tag $tag, Forum $forum)
    {
        $this->validate($request, ['q' => 'required|string|max:25']);
        $tags = $tag->lookupName(ltrim($request['q'], '#'));

        $result = $forum->getTagsWeight($tags->pluck('name')->toArray());
        $tags = [];

        // @todo do poprawy. szablon components.twig przyjmuje jako wartosc tablice tablic
        // natomiast getTagsWeight() zwraca tablice <nazwa tagu> => klucz
        foreach ($result as $name => $count) {
            $tags[] = [
                'name' => $name,
                'count' => $count
            ];
        }

        return view('components.tags')->with('tags', $tags);
    }

    /**
     * @param Request $request
     */
    public function valid(Request $request)
    {
        $this->validate($request, ['t' => 'required|string|max:25|tag|tag_creation:2']);
    }
}
