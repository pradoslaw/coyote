<?php

namespace Coyote\Http\Controllers\Adm\Forum;

use Coyote\Http\Controllers\Adm\BaseController;

class CategoryController extends BaseController
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return $this->view('adm.forum.category.home');
    }
}
