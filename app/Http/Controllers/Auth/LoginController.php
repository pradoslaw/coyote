<?php

namespace Coyote\Http\Controllers\Auth;

use Carbon\Carbon;
use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Coyote\Stream\Activities\Login as Stream_Login;
use Coyote\Stream\Activities\Logout as Stream_Logout;
use Validator;

class LoginController extends Controller
{
    /**
     * @var User
     */
    private $user;

    /**
     * LoginController constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        parent::__construct();
        $this->middleware('guest', ['except' => 'signout']);

        $this->user = $user;
    }

    /**
     * Widok formularza logowania
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->breadcrumb->push('Logowanie', route('login'));

        if (!$request->session()->has('url.intended')) {
            $request->session()->put('url.intended', url()->previous());
        }

        return $this->view('auth.login');
    }

    /**
     * Logowanie uzytkownika
     *
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function signin(Request $request)
    {
        $validator = Validator::make($request->only(['name', 'password']), [
            'name'                  => 'required|username',
            'password'              => 'required'
        ]);

        $user = null;

        $validator->after(function ($validator) use ($request, &$user) {
            $user = $this->user->findByName(mb_strtolower($request->get('name')));

            if (!$user) {
                $validator->errors()->add('name', trans('validation.user_exist'));
            }

            if ($user && (!$user->is_active || $user->is_blocked)) {
                $validator->errors()->add('name', trans('validation.user_active'));
            }
        });

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        if (Auth::attempt(['name' => $user->name, 'password' => $request->get('password')], true)) {
            // put information into the activity stream...
            stream(Stream_Login::class);
            return redirect()->intended(route('home'));
        }

        return back()->withInput()->withErrors(['password' => 'Podane hasło jest nieprawidłowe.']);
    }

    /**
     * Wylogowanie uzytkownika
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function signout()
    {
        $user = $this->user->findOrFail($this->userId);

        $user->ip = request()->ip();
        $user->browser = request()->browser(); // metoda browser() nie jest dostepna dla testow funkcjonalnych
        $user->visited_at = Carbon::now();
        $user->visits = auth()->user()->visits + 1;
        $user->save();

        stream(Stream_Logout::class);

        Auth::logout();
        return back();
    }
}
