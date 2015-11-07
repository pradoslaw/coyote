<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $this->breadcrumb->push('Forum', route('forum.home'));

        // generuje widok osob czytajacych dana strone
        $viewers = new \Coyote\Session\Viewers(new \Coyote\Session());

        return parent::view('forum.home')->with('viewers', $viewers->render($request->getRequestUri()));
    }

    /**
     * @return \Illuminate\View\View
     */
    public function getSubmit($forum)
    {
        $this->breadcrumb->push([
            'Forum'      => route('forum.home'),
            $forum       => route('forum.home') . "/$forum",
            'Nowy wątek' => route('forum.submit', ['forum' => $forum])
        ]);

        return parent::view('forum.submit');
    }

    public function postSubmit($forum)
    {
    }
}
