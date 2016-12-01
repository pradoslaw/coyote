<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Events\UserWasSaved;
use Coyote\Http\Factories\MailFactory;
use Coyote\Http\Forms\User as Forms;
use Coyote\Services\Stream\Activities\Update;
use Coyote\Services\Stream\Objects\Person;
use Coyote\Actkey;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;

class SettingsController extends BaseController
{
    use SettingsTrait, MailFactory;

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Ustawienia', route('user.settings'));

        $email = $this->auth->actkey()->value('email');
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
        return $this->createForm(Forms\SettingsForm::class, $this->auth, [
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

        $this->transaction(function () use ($form, $request) {
            if ($this->auth->email !== $request->get('email')) {
                $email = $request->get('email');

                // kasujemy poprzednie rekordu zwiazane z tym userem
                $this->auth->actkey()->delete();
                // przed zmiana e-maila trzeba wyslac link potwierdzajacy
                $url = Actkey::createLink($this->auth->id, $email);

                $this->getMailFactory()->queue(
                    'emails.user.change_email',
                    ['url' => $url],
                    function (Message $message) use ($email) {
                        $message->to($email);
                        $message->subject('Prosimy o potwierdzenie nowego adresu e-mail');
                    }
                );

                if ($this->auth->is_confirm) {
                    // user changed email. first, user has to confirm email before we save it.
                    $request['email'] = $this->auth->email;
                }
            }

            $this->auth->fill($form->all())->save();
            $preferences = json_decode($this->getSetting('job.preferences', '{}'), true);

            if (empty($preferences['city'])) {
                $preferences['city'] = $this->auth->location;
                $this->setSetting('job.preferences', json_encode($preferences));
            }

            stream(Update::class, new Person());
            event(new UserWasSaved($this->auth->id));
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
