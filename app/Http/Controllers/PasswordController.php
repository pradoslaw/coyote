<?php namespace Coyote\Http\Controllers;

class PasswordController extends Controller {

    /**
     * @return Response
     */
    public function getReset()
    {
        return view('reset');
    }

    public function getIndex()
    {
        return view('password');
    }

}
