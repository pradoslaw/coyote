<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Repositories\Contracts\SettingRepositoryInterface as Setting;
use Coyote\Repositories\Criteria\Topic\BelongsToForum;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    /**
     * @param \Coyote\Forum $forum
     * @param Request $request
     * @param Setting $setting
     * @return $this
     */
    public function index($forum, Request $request, Setting $setting)
    {
        // builds breadcrumb for this category
        $this->breadcrumb($forum);

        $this->pushForumCriteria();
        $forumList = $this->forum->forumList();

        $this->forum->skipCriteria(true);
        // execute query: get all subcategories that user can has access to
        $sections = $this->forum->groupBySections(auth()->id(), $request->session()->getId(), $forum->id);
        // get user settings based on user id or session id (for anonymous user)
        $settings = $setting->getAll(auth()->id(), $request->session()->getId());

        if ($request->has('perPage')) {
            $perPage = max(10, min($request->get('perPage'), 50));
            $setting->setItem('forum.topics_per_page', $perPage, auth()->id(), $request->session()->getId());
        } else {
            $perPage = isset($settings['forum.topics_per_page']) ? $settings['forum.topics_per_page'] : 20;
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

        $collapse = isset($settings['forum.collapse']) ? unserialize($settings['forum.collapse']) : [];

        return parent::view('forum.category')->with(
            compact('forumList', 'forum', 'topics', 'sections', 'collapse')
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
     * @param Setting $setting
     */
    public function section($forum, Request $request, Setting $setting)
    {
        $collapse = $setting->getItem('forum.collapse', auth()->id(), $request->session()->getId());
        if ($collapse !== null) {
            $collapse = unserialize($collapse);
        }

        $collapse[$forum->id] = (int) $request->input('flag');
        $setting->setItem('forum.collapse', serialize($collapse), auth()->id(), $request->session()->getId());
    }
}
