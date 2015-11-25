<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Coyote\Repositories\Contracts\SessionRepositoryInterface as Session;

class CategoryController extends Controller
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

        $viewers = new \Coyote\Session\Viewers($session, $request);

        return parent::view('forum.category')->with('viewers', $viewers->render($request->getRequestUri()));
    }
}
