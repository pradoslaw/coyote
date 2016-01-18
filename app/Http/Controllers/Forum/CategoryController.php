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

        if ($request->has('perPage')) {
            $perPage = max(10, min($request->get('perPage'), 50));
        } else {
            $perPage = 20;
        }

        // display topics for this category
        $this->topic->pushCriteria(new BelongsToForum($forum->id));
        // get topics according to given criteria
        $topics = $this->topic->paginate(
            auth()->id(),
            $request->getSession()->getId(),
            'topics.last_post_id',
            'DESC',
            $perPage
        );

        return parent::view('forum.category')->with(
            compact('forumList', 'forum', 'topics', 'sections')
        );
    }

    /**
     * @param $forum
     */
    public function mark($forum)
    {
        $this->forum->markAsRead($forum->id, auth()->id(), request()->session()->getId());
        $forums = $this->forum->where('parent_id', $forum->id)->get();

        foreach ($forums as $forum) {
            $this->forum->markAsRead($forum->id, auth()->id(), request()->session()->getId());
        }
    }
}
