<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;

class CategoryController extends Controller
{
    public function getIndex()
    {
        $this->breadcrumb->push('Forum', '/Forum');
        $this->breadcrumb->push('Python', '/Forum/Python');

        return parent::view('forum/category');
    }

    public function postIndex()
    {
    }
}
