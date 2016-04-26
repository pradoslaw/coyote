<?php

namespace Coyote\Http\Controllers\Adm\Forum;

use Coyote\Http\Controllers\Adm\BaseController;

class AccessController extends BaseController
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return $this->view('adm.forum.access.home');
    }
}
