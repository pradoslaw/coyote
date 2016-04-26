<?php

namespace Coyote\Http\Controllers\Adm;

class UserController extends BaseController
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return $this->view('adm.user.home');
    }
}
