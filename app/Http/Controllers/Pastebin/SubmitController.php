<?php

namespace Coyote\Http\Controllers\Pastebin;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Forms\PastebinForm;

class SubmitController extends Controller
{
    public function index()
    {
        //
    }

    /**
     * Zapis tresci pastebin do bazy danych
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(PastebinForm $pastebin)
    {
        //
    }
}
