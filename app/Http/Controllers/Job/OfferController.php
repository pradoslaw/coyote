<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;

class OfferController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Praca', route('job.home'));
        $this->breadcrumb->push('Lorem ipsum', route('job.offer'));

        return parent::view('job.offer');
    }
}
