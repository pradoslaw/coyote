<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;

class HomeController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Forum', route('forum.home'));

        return parent::view('forum.home');
    }

    /**
     * @return \Illuminate\View\View
     */
    public function getSubmit($forum)
    {
        $this->breadcrumb->push('Forum', route('forum.home'));
        $this->breadcrumb->push($forum, route('forum.home') . "/$forum");
        $this->breadcrumb->push('Nowy wątek', route('forum.submit', ['forum' => $forum]));

        return parent::view('forum.submit');
    }

    public function postSubmit($forum)
    {
    }
}
