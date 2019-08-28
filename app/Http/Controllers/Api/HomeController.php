<?php

namespace Coyote\Http\Controllers\Api;

use Illuminate\Routing\Controller;

class HomeController extends Controller
{
    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        // so far... redirect to homepage
        return redirect()->route('home');
    }
}
