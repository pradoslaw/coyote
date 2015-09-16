<?php namespace Coyote\Http\Controllers;

class RegisterController extends Controller {

    /**
     * @return Response
     */
    public function getIndex()
    {
        return view('register');
    }

    public function postIndex()
    {

    }

}
