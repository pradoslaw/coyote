<?php

namespace Coyote\Http\Controllers\Wiki;

use Coyote\Http\Controllers\Controller;

class WikiController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function category()
    {
        $this->breadcrumb->push('Delphi', '/Delphi');

        return $this->view('wiki/category');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function article()
    {
        $this->breadcrumb->push('Delphi', '/Delphi');
        $this->breadcrumb->push('Lorem ipsum lores', '/Delphi/Lorem_ipsum');

        return $this->view('wiki/article');
    }

    public function postIndex()
    {
    }
}
