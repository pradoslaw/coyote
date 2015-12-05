<?php

namespace Coyote\Http\Controllers\Auth;

use Coyote\Actkey;
use Coyote\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Coyote\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Coyote\Stream\Activities\Create as Stream_Create;
use Coyote\Stream\Objects\Person as Stream_Person;

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
            'name'                  => 'required|min:2|max:28|username|unique:users',
            'email'                 => 'required|email|max:255|unique:users',
            'password'              => 'required|confirmed|min:3',
            'human'                 => 'required'
        ]);

        DB::beginTransaction();

        try {
            $email = $request->input('email');

            $user = User::create([
                'name'     => $request->input('name'),
                'email'    => $email,
                'password' => bcrypt($request->input('password'))
            ]);

            $actkey = Actkey::create([
                'actkey'   => str_random(),
                'user_id'  => $user->id
            ]);

            // taki format linku zachowany jest ze wzgledu na wsteczna kompatybilnosc.
            // z czasem mozemy zmienic ten format aby wskazywal na /User/Confirm/Email/<id>/<actkey>
            $url = route('user.email') . '?id=' . $user->id . '&actkey=' . $actkey->actkey;

            Mail::queue('emails.signup', ['url' => $url], function ($message) use ($email) {
                $message->to($email);
                $message->subject('Dziękujemy za rejestrację. Potwierdź autentyczność swojego adresu e-mail');
            });

            Auth::login($user, true);
            stream(Stream_Create::class, new Stream_Person());

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return redirect()->intended(route('home'));
    }
}
