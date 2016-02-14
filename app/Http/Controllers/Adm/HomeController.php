<?php

namespace Coyote\Http\Controllers\Adm;

use Coyote\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;

class HomeController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        if ($request->session()->has('admin')) {
            return redirect()->route('adm.dashboard');
        }

        if ($request->isMethod('post')) {
            $this->validate($request, ['password' => 'required']);

            if (Auth::validate(['name' => auth()->user()->name, 'password' => $request->get('password')])) {
                $request->session()->put('admin', true);
                return redirect()->route('adm.dashboard');
            } else {
                return back()->withErrors('error');
            }
        }

        return view('adm.home');
    }
}
