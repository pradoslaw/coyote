<?php

namespace Coyote\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * @param $request Request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $viewers = new \Coyote\Session\Viewers(new \Coyote\Session(), $request);

        return view('home')->with('viewers', $viewers->render());
    }
}
