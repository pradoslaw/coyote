<?php

namespace Coyote\Http\Controllers\Pastebin;

use Coyote\Http\Controllers\Controller;

class HomeController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('pastebin.home');
    }

    /**
     * Wyswietla zawartosc pastebin
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id)
    {
        return view('pastebin.show');
    }

    /**
     * Zapis tresci pastebin do bazy danych
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save()
    {
        return back();
    }
}
