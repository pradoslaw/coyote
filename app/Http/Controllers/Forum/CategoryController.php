<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $this->breadcrumb->push('Forum', route('forum.home'));
        $this->breadcrumb->push('Python', '/Forum/Python');

        $viewers = new \Coyote\Session\Viewers(new \Coyote\Session(), $request);

        return parent::view('forum.category')->with('viewers', $viewers->render($request->getRequestUri()));
    }
}
