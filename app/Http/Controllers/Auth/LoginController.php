<?php

namespace Coyote\Http\Controllers\Auth;

use Carbon\Carbon;
use Coyote\Events\SuccessfulLogin;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Forms\Auth\LoginForm;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Coyote\Services\Stream\Activities\Login as Stream_Login;
use Coyote\Services\Stream\Activities\Logout as Stream_Logout;
use Coyote\Services\Stream\Activities\Throttle as Stream_Throttle;
use Coyote\Services\Stream\Actor;

class LoginController extends Controller
{
    use ThrottlesLogins;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('guest', ['except' => 'signout']);
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
            // send notification about new signin
            $this->fireSuccessfulLoginEvent($form->getRequest());

            return redirect()->intended(route('home'));
        }

        $lockedOut = $this->hasTooManyLoginAttempts($form->getRequest());

        if ($lockedOut) {
            $this->fireLockoutEvent($form->getRequest());

            return $this->sendLockoutResponse($form->getRequest());
        } else {
            // If the login attempt was unsuccessful we will increment the number of attempts
            // to login and redirect the user back to the login form. Of course, when this
            // user surpasses their maximum number of attempts they will get locked out.
            $this->incrementLoginAttempts($form->getRequest());

            // put failed action to activity stream
            stream((new Stream_Throttle(new Actor()))->setLogin($user->name));
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
        $this->auth->ip = $request->ip();
        // metoda browser() nie jest dostepna dla testow funkcjonalnych
        $this->auth->browser = $request->browser();

        $this->auth->visited_at = Carbon::now();
        $this->auth->is_online = false;

        $this->auth->save();

        stream(Stream_Logout::class);

        auth()->logout();
        return back();
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'name';
    }

    /**
     * Get the maximum number of login attempts for delaying further attempts.
     *
     * @return int
     */
    protected function maxLoginAttempts()
    {
        return 3;
    }

    /**
     * @param Request $request
     */
    private function fireSuccessfulLoginEvent(Request $request)
    {
        event(
            new SuccessfulLogin(
                auth()->user(),
                $request->ip(),
                substr((string) $request->header('User-Agent'), 0, 900)
            )
        );
    }
}
