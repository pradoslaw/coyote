<?php

namespace Coyote\Http\Controllers\Auth;

use Carbon\Carbon;
use Coyote\Events\SuccessfulLogin;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Forms\Auth\LoginForm;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Coyote\Services\Stream\Activities\Login as Stream_Login;
use Coyote\Services\Stream\Activities\Logout as Stream_Logout;
use Coyote\Services\Stream\Activities\Throttle as Stream_Throttle;
use Coyote\Services\Stream\Actor;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers{
        logout as traitLogout;
    }

    protected $redirectTo = '/';

    /**
     * @var int
     */
    protected $maxAttempts = 3;

    /**
     * @var int
     */
    protected $decayMinutes = 5;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * Widok formularza logowania
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Logowanie', route('login'));

        if (!$this->request->session()->has('url.intended')) {
            $this->request->session()->put('url.intended', url()->previous());
        }

        $form = $this->createForm(LoginForm::class, null, [
            'url' => route('login')
        ]);

        return $this->view('auth.login', compact('form'));
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     */
    protected function authenticated(Request $request, $user)
    {
        // put information into the activity stream...
        stream(Stream_Login::class);

        // send notification about new signin
        event(new SuccessfulLogin($user, $request->ip(), substr((string) $request->header('User-Agent'), 0, 900)));
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        // put failed action to activity stream
        stream((new Stream_Throttle(new Actor()))->setLogin($request->input($this->username())));

        throw ValidationException::withMessages([
            'password' => [trans('auth.failed')],
        ]);
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateLogin(Request $request)
    {
        $form = $this->createForm(LoginForm::class, $request->all());
        $form->validate();
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request), true
        );
    }

    /**
     * @return string
     */
    public function username()
    {
        return 'name';
    }

    /**
     * Wylogowanie uzytkownika
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        // user already logged out (maybe double click)?
        if (empty($this->auth)) {
            return $this->traitLogout($request);
        }

        $this->auth->ip = $request->ip();
        // metoda browser() nie jest dostepna dla testow funkcjonalnych
        $this->auth->browser = $request->browser();
        $this->auth->visits += 1;
        $this->auth->visited_at = Carbon::now();
        $this->auth->is_online = false;

        $this->auth->save();

        stream(Stream_Logout::class);

        return $this->traitLogout($request);
    }

    /**
     * The user has logged out of the application.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function loggedOut()
    {
        return back();
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        $field = filter_var($request->input('name'), FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        return [
            $field => $request->input('name'),
            'password' => $request->input('password'),
        ];
    }
}
