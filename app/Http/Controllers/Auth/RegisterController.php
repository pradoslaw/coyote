<?php

namespace Coyote\Http\Controllers\Auth;

use Coyote\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Coyote\User;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('guest');
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->breadcrumb->push('Rejestracja', '/register');

        return parent::view('auth.register');
    }

    /**
     * Obsluga formularza rejestracji uzytkownika
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function signup(Request $request)
    {
        $this->validate($request, [
            'name'                  => 'required|min:2|max:28|unique:users',
            'email'                 => 'required|email|max:255|unique:users',
            'password'              => 'required|confirmed|min:3'
        ]);

        $user = User::create([
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'password' => bcrypt($request->input('password'))
        ]);

        Auth::login($user, true);

        return redirect()->intended(route('home'));
    }
}
