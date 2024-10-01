<?php

namespace Coyote\Http\Controllers\Auth;

use Carbon\Carbon;
use Coyote\Actkey;
use Coyote\Domain\RouteVisits;
use Coyote\Events\UserSaved;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Forms\Auth\RegisterForm;
use Coyote\Mail\UserRegistered;
use Coyote\Services\FormBuilder\Form;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Objects\Person as Stream_Person;
use Coyote\User;
use Illuminate\Contracts\Mail\MailQueue;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Jenssegers\Agent\Agent;

class RegisterController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('guest');
    }

    public function index(RouteVisits $visits): View
    {
        $this->breadcrumb->push('Rejestracja', route('register'));
        $agent = new Agent();
        if (!$agent->isRobot($this->request->userAgent())) {
            $visits->visit($this->request->path(), Carbon::now()->toDateString());
        }
        return $this->view('auth.register', ['form' => $this->form()]);
    }

    private function form(): Form
    {
        return $this->createForm(RegisterForm::class, null, ['url' => route('register')]);
    }

    public function signup(RegisterForm $form): RedirectResponse
    {
        $request = $form->getRequest();

        $this->transaction(function () use ($request) {
            $user = User::forceCreate([
                'name'                 => $request->input('name'),
                'email'                => $request->input('email'),
                'password'             => bcrypt($request->input('password')),
                'guest_id'             => $request->session()->get('guest_id'),
                'marketing_agreement'  => $request->input('marketing_agreement'),
                'newsletter_agreement' => true,
                'allow_smilies'        => true,
            ]);

            app(MailQueue::class)
                ->to($request->input('email'))
                ->send(new UserRegistered(Actkey::createLink($user->id)));

            auth()
                ->login($user, true);

            stream(Stream_Create::class, new Stream_Person());

            event(new UserSaved($user));
        });

        return redirect()
            ->intended(route('user.home'))
            ->with('success', 'Konto zostało utworzone. Na podany adres e-mail, przesłany został link aktywacyjny.');
    }
}
