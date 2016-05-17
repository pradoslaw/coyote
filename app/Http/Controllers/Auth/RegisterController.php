<?php

namespace Coyote\Http\Controllers\Auth;

use Coyote\Actkey;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Factories\MailFactory;
use Coyote\Http\Forms\Auth\RegisterForm;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Objects\Person as Stream_Person;

class RegisterController extends Controller
{
    use MailFactory;

    /**
     * @var UserRepository
     */
    private $user;

    /**
     * RegisterController constructor.
     * @param UserRepository $user
     */
    public function __construct(UserRepository $user)
    {
        parent::__construct();
        $this->middleware('guest');

        $this->user = $user;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Rejestracja', route('register'));
        $form = $this->createForm(RegisterForm::class, null, [
            'url' => route('register')
        ]);

        return $this->view('auth.register', compact('form'));
    }

    /**
     * Obsluga formularza rejestracji uzytkownika
     *
     * @param  RegisterForm  $form
     * @return \Illuminate\Http\RedirectResponse
     */
    public function signup(RegisterForm $form)
    {
        $request = $form->getRequest();

        $this->transaction(function () use ($request) {
            $email = $request->input('email');

            $user = $this->user->create([
                'name'     => $request->input('name'),
                'email'    => $email,
                'password' => bcrypt($request->input('password'))
            ]);

            $url = Actkey::createLink($user->id);

            $this->getMailFactory()->queue('emails.signup', ['url' => $url], function ($message) use ($email) {
                $message->to($email);
                $message->subject('Dziękujemy za rejestrację. Potwierdź autentyczność swojego adresu e-mail');
            });

            auth()->login($user, true);
            stream(Stream_Create::class, new Stream_Person());
        });

        return redirect()
            ->intended(route('user.home'))
            ->with('success', 'Konto zostało utworzone. Na podany adres e-mail, przesłany został link aktywacyjny.');
    }
}
