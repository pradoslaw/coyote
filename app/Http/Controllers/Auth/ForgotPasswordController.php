<?php

namespace Coyote\Http\Controllers\Auth;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Requests\ForgotPasswordRequest;
use Coyote\Services\Stream\Activities\ForgotPassword;
use Coyote\Services\Stream\Actor;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */
    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->middleware('guest');
    }

    /**
     * Formularz generuje link umozliwiajacy reset hasla
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showLinkRequestForm()
    {
        $this->breadcrumb->push('Odzyskiwanie hasła', url('Password'));

        return $this->view('auth.password');
    }

    /**
     * Formularz powoduje wyslanie linku umozliwiajacego zmiane hasla
     *
     * @param ForgotPasswordRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLinkEmail(ForgotPasswordRequest $request)
    {
        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = $this->broker()->sendResetLink(
            // aby wygenerowac link konto musi byc aktywne oraz e-mail musi byc wczesniej potwierdzony
            $request->only('email') + ['is_confirm' => 1]
        );

        if ($response === Password::RESET_LINK_SENT) {
            stream((new ForgotPassword(new Actor()))->setEmail($request->input('email')));

            return back()->with('success', trans($response));
        }

        // If an error was returned by the password broker, we will get this message
        // translated so we can notify a user of the problem. We'll redirect back
        // to where the users came from so they can attempt this process again.
        return back()->withErrors(
            ['email' => trans($response)]
        );
    }
}
