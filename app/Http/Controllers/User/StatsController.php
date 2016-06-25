<?php

namespace Coyote\Http\Controllers\User;

use Coyote\User;

class StatsController extends BaseController
{
    use HomeTrait;

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return $this->view('user.stats');
    }
}
