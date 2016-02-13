<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;

class HomeController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return $this->view('job.home');
    }
}
