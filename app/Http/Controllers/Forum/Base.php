<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;

trait Base
{
    /**
     * Builds breadcrumb for this category
     *
     * @param \Coyote\Forum $forum
     */
    public function breadcrumb($forum)
    {
        $this->breadcrumb->push('Forum', route('forum.home'));

        if ($forum->parent_id) {
            $parent = $this->forum->find($forum->parent_id, ['path', 'name']);
            $this->breadcrumb->push($parent->name, route('forum.category', [$parent->path]));
        }

        $this->breadcrumb->push($forum->name, route('forum.category', [$forum->path]));
    }

    /**
     * Applies repository criteria
     */
    public function pushForumCriteria()
    {
        if (auth()->check()) {
            $groupsId = auth()->user()->groups()->lists('id');

            if ($groupsId) {
                $this->forum->pushCriteria(new OnlyThoseWithAccess($groupsId->toArray()));
            }
        }
    }

    public function authorizeForum($forum)
    {
        if ((auth()->guest() && !$forum->enable_anonymous) || $forum->is_locked) {
            abort(403);
        }
    }
}
