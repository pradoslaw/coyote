<?php

namespace Coyote\Http\Controllers\Auth;

use Coyote\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Coyote\User;

class LoginController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('guest', ['except' => 'signout']);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->breadcrumb->push('Logowanie', route('login'));

        return parent::view('auth.login');
    }

    /**
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function signin(Request $request)
    {
        $this->validate($request, [
            'name'                  => 'required|username',
            'password'              => 'required'
        ]);

        if (Auth::attempt($request->only('name', 'password') + ['is_active' => 1], $request->has('remember'))) {
            return redirect()->intended(route('home'));
        }

        // W starej wersji 4programmers.net hasla byly hashowane przy pomocy sha256 + sol. Jezeli w bazie
        // danych jest stary hash, to zmieniamy hasha i zapisujemy do bazy danych
        if (env('APP_ENV') == 'prod') {
            $user = User::where('name', $request->input('name'))->first();

            if ($user && $user->salt && $user->password === hash('sha256', $user->salt . $request->input('password'))) {
                $user->password = bcrypt($request->input('password'));
                $user->salt = null;
                $user->save();

                Auth::login($user);
                return redirect()->intended(route('home'));
            }
        }

        return back()->withInput()->withErrors(['name' => 'Konto nie istnieje lub hasło jest nieprawidłowe']);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function signout()
    {
        Auth::logout();
        return back();
    }
}
