<?php

namespace Coyote\Http\Controllers\Adm;

use Coyote\Mail\Mailing;
use Coyote\Repositories\Contracts\MailingRepositoryInterface as MailingRepository;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Http\Request;

class MailingController extends BaseController
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Mailing', route('adm.mailing'));

        return $this->view('adm.mailing');
    }

    /**
     * @param MailingRepository $mailing
     * @param Request $request
     * @param Mailer $mailer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submit(MailingRepository $mailing, Request $request, Mailer $mailer)
    {
        /** @var \Coyote\Mailing $result */
        foreach ($mailing->all() as $result) {
            $mailer
                ->to($result->email)
                ->send(new Mailing($result->id, $request->input('subject'), $request->input('text')));
        }

        return back()->with('success', 'Mailing został wysłany.');
    }
}
