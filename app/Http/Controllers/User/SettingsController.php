<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Factories\MailFactory;
use Coyote\Http\Forms\User as Forms;
use Coyote\Services\Stream\Activities\Update;
use Coyote\Services\Stream\Objects\Person;
use Coyote\Actkey;
use Illuminate\Http\Request;

class SettingsController extends BaseController
{
    use SettingsTrait, MailFactory;

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Ustawienia', route('user.settings'));

        $email = auth()->user()->actkey()->value('email');
        $form = $this->getForm();

        return $this->view('user.settings', [
            'email'             => $email,
            'form'              => $form
        ]);
    }

    /**
     * @return \Coyote\Services\FormBuilder\Form
     */
    protected function getForm()
    {
        return $this->createForm(Forms\SettingsForm::class, auth()->user(), [
            'url' => route('user.settings')
        ]);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save()
    {
        $form = $this->getForm();
        $form->validate();

        $request = $form->getRequest();

        \DB::transaction(function () use ($request) {
            /**
             * @var \Coyote\User $user
             */
            $user = auth()->user();

            if ($user->email !== $request->get('email')) {
                $email = $request->get('email');

                // kasujemy poprzednie rekordu zwiazane z tym userem
                $user->actkey()->delete();
                // przed zmiana e-maila trzeba wyslac link potwierdzajacy
                $url = Actkey::createLink($user->id, $email);

                $this->getMailFactory()->queue('emails.email', ['url' => $url], function ($message) use ($email) {
                    $message->to($email);
                    $message->subject('Prosimy o potwierdzenie nowego adresu e-mail');
                });

                if ($user->is_confirm) {
                    $request['email'] = $user->email;
                }
            }

            $user->fill($request->all())->save();
            stream(Update::class, new Person());
        });

        return back()->with('success', 'Zmiany zostały poprawie zapisane');
    }

    /**
     * @param Request $request
     */
    public function ajax(Request $request)
    {
        $name = array_keys($request->all())[0];
        $name = trim(strip_tags(htmlspecialchars($name)));

        if (!empty($name)) {
            $value = trim(strip_tags(htmlspecialchars($request->get($name))));

            $this->setSetting(str_replace('_', '.', $name), $value);
        }
    }
}
