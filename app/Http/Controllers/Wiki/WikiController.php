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

    public function postIndex()
    {
    }
}
