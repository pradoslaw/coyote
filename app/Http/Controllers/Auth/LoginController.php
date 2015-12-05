<?php

namespace Coyote\Http\Controllers\Auth;

use Carbon\Carbon;
use Coyote\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Coyote\User;
use Coyote\Stream\Activities\Login as Stream_Login;
use Coyote\Stream\Activities\Logout as Stream_Logout;

class LoginController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('guest', ['except' => 'signout']);
    }

    /**
     * Widok formularza logowania
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->breadcrumb->push('Logowanie', route('login'));

        return parent::view('auth.login');
    }

    /**
     * Logowanie uzytkownika
     *
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
            // put information into the activity stream...
            stream(Stream_Login::class);
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
     * Wylogowanie uzytkownika
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function signout()
    {
        $user = User::findOrFail(auth()->user()->id);

        $user->ip = request()->ip();
        $user->browser = filter_var(request()->header('User-Agent'), FILTER_SANITIZE_STRING);
        $user->visited_at = Carbon::now();
        $user->visits = auth()->user()->visits + 1;
        $user->save();

        stream(Stream_Logout::class);

        Auth::logout();
        return back();
    }
}
