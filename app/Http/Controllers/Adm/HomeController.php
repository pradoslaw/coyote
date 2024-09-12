<?php

namespace Coyote\Http\Controllers\Adm;

use Coyote\Http\Controllers\Controller;
use Coyote\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('admin')) {
            return redirect()->route('adm.dashboard');
        }
        /** @var User $user */
        $user = auth()->user();
        if ($request->isMethod('post')) {
            $this->validate($request, ['password' => 'required']);
            if (auth()->validate(['name' => $user->name, 'password' => $request->get('password')])) {
                $request->session()->put('admin', true);
                return redirect()->intended(route('adm.dashboard'));
            }
            return back()->withErrors('error');
        }
        return view('adm.home', [
            'user' => [
                'name'  => $user->name,
                'photo' => $user->photo,
            ],
        ]);
    }
}
