<?php

namespace Coyote\Http\Controllers;

class HomeController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('home');
    }
}
