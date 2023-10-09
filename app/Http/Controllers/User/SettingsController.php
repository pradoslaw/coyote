<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Actkey;
use Coyote\Domain\User\UserSettings;
use Coyote\Events\UserSaved;
use Coyote\Http\Controllers\User\Menu\SettingsMenu;
use Coyote\Http\Forms\User\SettingsForm;
use Coyote\Mail\EmailConfirmation;
use Coyote\Services\FormBuilder\Form;
use Coyote\Services\Stream\Activities\Update;
use Coyote\Services\Stream\Objects\Person;
use Coyote\View\Twig\TwigLiteral;
use Illuminate\Contracts\Mail\MailQueue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends BaseController
{
    use SettingsMenu;

    public function index(): View
    {
        $this->breadcrumb->push('Ustawienia', route('user.settings'));
        $email = $this->auth->actkey()->value('email');
        $form = $this->getForm();
        if ($email) {
            $form->get('email')->setAttr(['data-popover' => json_encode([
                'message'   => "Na adres $email wysłaliśmy link umożliwiający zmianę adresu e-mail.",
                'placement' => 'top'
            ])]);
        }
        return $this->view('user.settings', [
            'email'             => $email,
            'form'              => $form,
            'informationClause' => TwigLiteral::fromHtml((new UserSettings())->informationClause())
        ]);
    }

    protected function getForm(): Form
    {
        return $this->createForm(SettingsForm::class, $this->auth, [
            'url' => route('user.settings')
        ]);
    }

    public function save(): RedirectResponse
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
                    $this->auth->email = $this->auth->getRawOriginal('email');
                }
            }
            $this->auth->save();
            stream(Update::class, new Person());
            event(new UserSaved($this->auth));
        });

        return back()->with('success', 'Zmiany zostały poprawie zapisane');
    }

    public function ajax(Request $request): void
    {
        $name = array_keys($request->all())[0];
        $name = trim(strip_tags(htmlspecialchars($name)));
        if (!empty($name)) {
            $value = trim(strip_tags(htmlspecialchars($request->get($name))));
            $this->setSetting(str_replace('_', '.', $name), $value);
        }
    }

    private function sendConfirmationEmail(string $email): void
    {
        // kasujemy poprzednie rekordu zwiazane z tym userem
        $this->auth->actkey()->delete();
        // przed zmiana e-maila trzeba wyslac link potwierdzajacy
        $url = Actkey::createLink($this->auth->id, $email);

        app(MailQueue::class)->to($email)->queue(new EmailConfirmation($url));
    }
}
