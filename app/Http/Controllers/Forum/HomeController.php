<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function getIndex()
    {
        $this->breadcrumb->push('Forum', '/Forum');

        return parent::view('forum/home');
    }

    public function getSubmit()
    {
        $this->breadcrumb->push('Forum', '/Forum');
        $this->breadcrumb->push('Python', '/Forum/Python');
        $this->breadcrumb->push('Nowy wątek', '/Forum/Python/Submit');

        return parent::view('forum/submit');
    }
}
