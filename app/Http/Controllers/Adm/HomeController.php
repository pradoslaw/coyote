<?php

namespace Coyote\Http\Controllers\Adm;

use Coyote\Http\Controllers\Controller;

class HomeController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('adm.home');
    }
}
