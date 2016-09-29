<?php

namespace Coyote\Http\Controllers\Wiki;

class HomeController extends BaseController
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Kompendium', route('wiki.home'));

        return $this->view('wiki.home');
    }
}
