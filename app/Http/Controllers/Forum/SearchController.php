<?php

namespace Coyote\Http\Controllers\Forum;

class SearchController extends BaseController
{
    public function index()
    {
        // create forum list for current user (according to user's privileges)
        $this->pushForumCriteria();
        $forumList = $this->forum->forumList();

        return $this->view('forum.search')->with(compact('forumList'));
    }
}
