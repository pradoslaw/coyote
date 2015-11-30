<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * @param Request $request
     * @return $this
     */
    public function index(Request $request)
    {
        $this->breadcrumb->push('Forum', route('forum.home'));
        $this->breadcrumb->push('Python', '/Forum/Python');

        $viewers = app('Session\Viewers');

        return parent::view('forum.category')->with('viewers', $viewers->render($request->getRequestUri()));
    }
}
