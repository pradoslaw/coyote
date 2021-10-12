<?php

namespace Coyote\Http\Controllers\Guide;

use Coyote\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return $this->view('guide.home');
    }
}
