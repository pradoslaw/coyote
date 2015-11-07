<?php

namespace Coyote\Http\Controllers;

class HomeController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $viewers = new \Coyote\Session\Viewers(new \Coyote\Session());

        return view('home')->with('viewers', $viewers->render());
    }
}
