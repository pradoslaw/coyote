<?php

namespace Coyote\Http\Controllers\Auth;

use Carbon\Carbon;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Forms\Auth\LoginForm;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Illuminate\Http\Request;
use Coyote\Services\Stream\Activities\Login as Stream_Login;
use Coyote\Services\Stream\Activities\Logout as Stream_Logout;

class LoginController extends Controller
{
    /**
     * @var UserRepository
     */
    private $user;

    /**
     * LoginController constructor.
     * @param UserRepository $user
     */
    public function __construct(UserRepository $user)
    {
        parent::__construct();
        $this->middleware('guest', ['except' => 'signout']);

        $this->user = $user;
    }

    /**
     * Widok formularza logowania
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $this->breadcrumb->push('Logowanie', route('login'));

        if (!$request->session()->has('url.intended')) {
            $request->session()->put('url.intended', url()->previous());
        }

        $form = $this->createForm(LoginForm::class, null, [
            'url' => route('login')
        ]);

        return $this->view('auth.login', compact('form'));
    }

    /**
     * Logowanie uzytkownika
     *
     * @param LoginForm $form
     * @return \Illuminate\Http\RedirectResponse
     */
    public function signin(LoginForm $form)
    {
        $user = $form->getUser();

        if (auth()->attempt(['name' => $user->name, 'password' => $form->password->getValue()], true)) {
            // put information into the activity stream...
            stream(Stream_Login::class);
            return redirect()->intended(route('home'));
        }

        return back()->withInput()->withErrors(['password' => 'Podane hasło jest nieprawidłowe.']);
    }

    /**
     * Wylogowanie uzytkownika
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function signout(Request $request)
    {
        $user = $this->user->findOrFail($this->userId);

        $user->ip = request()->ip();
        $user->browser = $request->browser(); // metoda browser() nie jest dostepna dla testow funkcjonalnych
        $user->visited_at = Carbon::now();
        $user->visits = auth()->user()->visits + 1;
        $user->save();

        stream(Stream_Logout::class);

        auth()->logout();
        return back();
    }
}
