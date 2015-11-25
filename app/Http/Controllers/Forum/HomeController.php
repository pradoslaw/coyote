<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Coyote\Repositories\Contracts\SessionRepositoryInterface as Session;

class HomeController extends Controller
{
    /**
     * @param Request $request
     * @param Session $session
     * @return $this
     */
    public function index(Request $request, Session $session)
    {
        $this->breadcrumb->push('Forum', route('forum.home'));

        // generuje widok osob czytajacych dana strone
        $viewers = new \Coyote\Session\Viewers($session, $request);

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
