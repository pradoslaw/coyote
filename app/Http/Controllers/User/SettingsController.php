<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Events\UserSaved;
use Coyote\Http\Factories\MailFactory;
use Coyote\Http\Forms\User as Forms;
use Coyote\Mail\EmailConfirmation;
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

        $email = $this->auth->actkey()->value('email');
        $form = $this->getForm();

        if ($email) {
            $popover = [
                'message' => "Na adres $email wysłaliśmy link umożliwiający zmianę adresu e-mail.",
                'placement' => 'top',
                'offset' => '0,10px'
            ];

            $form->get('email')->setAttr(['data-popover' => json_encode($popover)]);
        }

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

        // we use forceFill() to fill fields that are NOT in $fillable model's array.
        // we can do that because $form->all() returns only fields in form. $request->all() returns
        // all fields in HTTP POST so it's not secure.
        $this->auth->forceFill(array_except($form->all(), ['submit']));

        $this->transaction(function () use ($form, $request) {
            if ($this->auth->isDirty('email')) {
                $this->sendConfirmationEmail($request->get('email'));

                // user changed email. first, user has to confirm email before we save it.
                if ($this->auth->is_confirm) {
                    $this->auth->email = $this->auth->getOriginal('email');
                }
            }

            $this->auth->save();

            stream(Update::class, new Person());

            event(new UserSaved($this->auth));
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

    /**
     * @param string $email
     */
    private function sendConfirmationEmail(string $email)
    {
        // kasujemy poprzednie rekordu zwiazane z tym userem
        $this->auth->actkey()->delete();
        // przed zmiana e-maila trzeba wyslac link potwierdzajacy
        $url = Actkey::createLink($this->auth->id, $email);

        $this->getMailFactory()->to($email)->queue(new EmailConfirmation($url));
    }
}
