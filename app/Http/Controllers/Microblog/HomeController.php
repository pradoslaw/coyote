<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;

class HomeController extends Controller
{
    /**
     * @return Response
     */
    public function getIndex()
    {
        $this->breadcrumb->push('Mikroblog', '/Microblog');

        return parent::view('microblog/home');
    }

    public function postIndex()
    {
    }
}
