<?php

namespace Coyote\Http\Controllers\Auth;

use Coyote\Actkey;
use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Objects\Person as Stream_Person;

class RegisterController extends Controller
{
    /**
     * @var User
     */
    private $user;

    /**
     * RegisterController constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        parent::__construct();
        $this->middleware('guest');

        $this->user = $user;
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->breadcrumb->push('Rejestracja', route('register'));

        return $this->view('auth.register');
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
            'name'                  => 'required|min:2|max:28|username|user_unique',
            'email'                 => 'required|email|max:255|unique:users,email,NULL,id,is_confirm,1',
            'password'              => 'required|confirmed|min:3',
            'human'                 => 'required'
        ]);

        DB::transaction(function () use ($request) {
            $email = $request->input('email');

            $user = $this->user->create([
                'name'     => $request->input('name'),
                'email'    => $email,
                'password' => bcrypt($request->input('password'))
            ]);

            $url = Actkey::createLink($user->id);

            Mail::queue('emails.signup', ['url' => $url], function ($message) use ($email) {
                $message->to($email);
                $message->subject('Dziękujemy za rejestrację. Potwierdź autentyczność swojego adresu e-mail');
            });

            Auth::login($user, true);
            stream(Stream_Create::class, new Stream_Person());
        });

        return redirect()
            ->intended(route('user.home'))
            ->with('success', 'Konto zostało utworzone. Na podany adres e-mail, przesłany został link aktywacyjny.');
    }
}
