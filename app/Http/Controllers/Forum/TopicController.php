<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Coyote\Repositories\Contracts\SessionRepositoryInterface as Session;

class TopicController extends Controller
{
    /**
     * @param Request $request
     * @param Session $session
     * @return $this
     */
    public function index(Request $request, Session $session)
    {
        $this->breadcrumb->push('Forum', route('forum.home'));
        $this->breadcrumb->push('Python', '/Forum/Python');
        $this->breadcrumb->push('Python - wybór "najlepszego" GUI cross-platform', '/Forum/Python/Test');

        $viewers = new \Coyote\Session\Viewers($session, $request);

        return parent::view('forum.topic')->with('viewers', $viewers->render($request->getRequestUri()));
    }
}
