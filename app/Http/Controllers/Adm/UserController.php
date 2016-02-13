<?php

namespace Coyote\Http\Controllers\Adm;

class UserController extends BaseController
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return $this->view('adm.user.home');
    }
}
