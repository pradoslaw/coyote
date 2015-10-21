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
        $this->breadcrumb->push('Forum', '/Forum');

        return parent::view('forum.home');
    }

    /**
     * @return \Illuminate\View\View
     */
    public function getSubmit($forum)
    {
        $this->breadcrumb->push('Forum', '/Forum');
        $this->breadcrumb->push($forum, "/Forum/$forum");
        $this->breadcrumb->push('Nowy wątek', "/Forum/Submit/$forum");

        return parent::view('forum.submit');
    }

    public function postSubmit($forum)
    {

    }
}
