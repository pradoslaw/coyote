<?php

namespace Coyote\Http\Controllers;

class SearchController extends Controller
{
    public function index()
    {
        $this->breadcrumb->push('Szukaj', route('search'));

        return $this->view('search');
    }
}
