<?php

namespace Coyote\Http\Controllers;

use Gate;

class HomeController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }
}
