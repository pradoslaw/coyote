<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Repositories\Criteria\Topic\BelongsToForum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
            $this->setSetting('forum.topics_per_page', $perPage);
        } else {
            $perPage = $this->getSetting('forum.topics_per_page', 20);
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

        // we need to get an information about flagged topics. that's how moderators can notice
        // that's something's wrong with posts.
        if (Gate::allows('delete', $forum)) {
            $flags = app()->make('FlagRepository')->takeForTopics($topics->groupBy('id')->keys()->toArray());
        }

        $collapse = $this->getSetting('forum.collapse') ? unserialize($this->getSetting('forum.collapse')) : [];

        return $this->view('forum.category')->with(
            compact('forumList', 'forum', 'topics', 'sections', 'collapse', 'flags')
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

    /**
     * Set category visibility
     *
     * @param \Coyote\Forum $forum
     * @param Request $request
     */
    public function section($forum, Request $request)
    {
        $collapse = $this->getSetting('forum.collapse');
        if ($collapse !== null) {
            $collapse = unserialize($collapse);
        }

        $collapse[$forum->id] = (int) $request->input('flag');
        $this->setSetting('forum.collapse', serialize($collapse));
    }
}
