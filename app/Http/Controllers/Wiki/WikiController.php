<?php

namespace Coyote\Http\Controllers\Wiki;

use Coyote\Http\Controllers\Controller;

class WikiController extends Controller
{
    /**
     * @return Response
     */
    public function category()
    {
        $this->breadcrumb->push('Delphi', '/Delphi');

        return parent::view('wiki/category');
    }

    public function article()
    {
        $this->breadcrumb->push('Delphi', '/Delphi');
        $this->breadcrumb->push('Lorem ipsum lores', '/Delphi/Lorem_ipsum');

        return parent::view('wiki/article');
    }

    public function postIndex()
    {
    }
}
