<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Repositories\Criteria\Topic\BelongsToForum;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as Topic;
use Illuminate\Http\Request;
use Coyote\Http\Controllers\Controller;

class CategoryController extends BaseController
{
    /**
     * @param \Coyote\Forum $forum
     * @param Request $request
     * @return $this
     */
    public function index($forum, Request $request)
    {
        // builds breadcrumb for this category
        $this->breadcrumb($forum);

        $this->pushForumCriteria();
        $forumList = $this->forum->forumList();

        $this->forum->skipCriteria(true);
        // execute query: get all subcategories that user can has access to
        $sections = $this->forum->groupBySections(auth()->id(), $request->session()->getId(), $forum->id);

        // display topics for this category
        $this->topic->pushCriteria(new BelongsToForum($forum->id));
        $topics = $this->topic->paginate(auth()->id(), $request->getSession()->getId());

        return parent::view('forum.category')->with(
            compact('forumList', 'forum', 'topics', 'sections')
        );
    }
}
