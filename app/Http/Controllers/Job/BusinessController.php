<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;

class BusinessController extends Controller
{
    public function show()
    {
        return $this->view('job.business');
    }
}
