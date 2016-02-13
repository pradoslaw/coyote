<?php

namespace Coyote\Http\Controllers\Adm;

use Coyote\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return $this->view('adm.dashboard');
    }
}
