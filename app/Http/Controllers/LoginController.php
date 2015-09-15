<?php namespace Coyote\Http\Controllers;

class LoginController extends Controller {

    /**
     * @return Response
     */
    public function index()
    {
        return view('login');
    }

}
