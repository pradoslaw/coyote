<?php

namespace Coyote\Http\Controllers\Auth;

use Coyote\Actkey;
use Coyote\Events\UserWasSaved;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Factories\MailFactory;
use Coyote\Http\Forms\Auth\RegisterForm;
use Coyote\Mail\UserRegistered;
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

        $form = $this->getForm();

        return $this->view('auth.register', compact('form'));
    }

    /**
     * @return \Coyote\Services\FormBuilder\Form
     */
    private function getForm()
    {
        return $this->createForm(RegisterForm::class, null, [
            'url' => route('register')
        ]);
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

            $user = $this->user->newUser([
                'name'     => $request->input('name'),
                'email'    => $email,
                'password' => bcrypt($request->input('password')),
                'guest_id' => $request->session()->get('guest_id')
            ]);

            $url = Actkey::createLink($user->id);
            $this->getMailFactory()->to($email)->send(new UserRegistered($url));

            auth()->login($user, true);

            stream(Stream_Create::class, new Stream_Person());

            event(new UserWasSaved($user));
        });

        return redirect()
            ->intended(route('user.home'))
            ->with('success', 'Konto zostało utworzone. Na podany adres e-mail, przesłany został link aktywacyjny.');
    }
}
