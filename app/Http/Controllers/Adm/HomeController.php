<?php

namespace Coyote\Http\Controllers\Adm;

use Coyote\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        if ($request->session()->has('admin')) {
            return redirect()->route('adm.dashboard');
        }

        if ($request->isMethod('post')) {
            $this->validate($request, ['password' => 'required']);

            if (auth()->validate(['name' => auth()->user()->name, 'password' => $request->get('password')])) {
                $request->session()->put('admin', true);

                return redirect()->intended(route('adm.dashboard'));
            } else {
                return back()->withErrors('error');
            }
        }

        return view('adm.home');
    }
}
