<?php

namespace Coyote\Http\Controllers\User;

use Coyote\User;

class AcceptsController extends BaseController
{
    use HomeTrait;

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return $this->view('user.accepts');
    }
}
