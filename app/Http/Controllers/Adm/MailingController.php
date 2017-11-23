<?php

namespace Coyote\Http\Controllers\Adm;

use Carbon\Carbon;
use Coyote\Http\Forms\MailingForm;
use Coyote\Mail\Mailing;
use Coyote\Repositories\Contracts\MailingRepositoryInterface as MailingRepository;
use Illuminate\Contracts\Mail\Mailer;

class MailingController extends BaseController
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Mailing', route('adm.mailing'));

        return $this->view('adm.mailing')->with('form', $this->createForm(MailingForm::class));
    }

    /**
     * @param MailingRepository $mailing
     * @param MailingForm $form
     * @param Mailer $mailer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submit(MailingRepository $mailing, MailingForm $form, Mailer $mailer)
    {
        $recipients = $form->get('is_demo')->isChecked() ? [$this->auth] : $mailing->all();
        $second = 1;

        foreach ($recipients as $recipient) {
            $mailer
                ->to($recipient->email)
                ->later(
                    Carbon::now()->addSecond($second++),
                    new Mailing($recipient->id, $form->get('subject')->getValue(), $form->get('text')->getValue())
                );
        }

        return back()->with('success', 'Mailing został wysłany.')->withInput();
    }
}
