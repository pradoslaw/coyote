<?php


namespace Coyote\Http\Controllers;


class ProjectxController extends Controller
{
    public function index()
    {
        $this->breadcrumb->push('Lorem ipsum');

        return $this->view('projectx');
    }
}
