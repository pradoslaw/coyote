<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Coyote\User;

class PromptController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('components.prompt')->with('users', User::all());
    }
}
