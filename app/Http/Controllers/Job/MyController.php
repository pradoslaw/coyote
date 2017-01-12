<?php

namespace Coyote\Http\Controllers\Job;

class MyController extends BaseController
{
    public function index()
    {
        $this->tab = 'my';

        return $this->view('job.my');
    }
}
