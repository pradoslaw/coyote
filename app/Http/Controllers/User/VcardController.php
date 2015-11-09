<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;

class VcardController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index($id)
    {
        return view('components.vcard');
    }
}
