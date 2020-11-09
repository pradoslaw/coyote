<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Factories\MailFactory;
use Coyote\Http\Forms\Job\ReferForm;
use Coyote\Job;
use Coyote\Mail\OfferReferred;
use Coyote\Mail\OfferReferredPerson;
use Coyote\Services\UrlBuilder;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Objects\Job as Stream_Job;
use Coyote\Services\Stream\Objects\Refer as Stream_Refer;

class ReferController extends BaseController
{
    use MailFactory;

    /**
     * @param Job $job
     * @return \Illuminate\View\View
     */
    public function index($job)
    {
        $this->breadcrumb->push([
            'Praca' => route('job.home'),
            $job->title => UrlBuilder::job($job),
            "Poleć znajomego na stanowisko {$job->title}" => null
        ]);

        $form = $this->createForm(ReferForm::class);

        if ($this->userId) {
            $form->get('email')->setValue($this->auth->email);
            $form->get('name')->setValue($this->auth->name);
        }

        return $this->view('job.refer')->with(['form' => $form, 'job' => $job]);
    }

    /**
     * @param Job $job
     * @param ReferForm $form
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save($job, ReferForm $form)
    {
        $this->transaction(function () use ($job, $form) {
            $target = (new Stream_Job)->map($job);

            $job->refers()->create($form->all() + ['guest_id' => $this->guestId]);
            $mailer = $this->getMailFactory();

            $mailer->to($job->email ?: $job->user->email)->send((new OfferReferred($job))->with($form->all()));
            $mailer->to($form->get('friend_email')->getValue())->send((new OfferReferredPerson($job))->with($form->all()));

            stream(Stream_Create::class, new Stream_Refer(['displayName' => $form->get('friend_name')->getValue()]), $target);
        });

        return redirect()
            ->to(UrlBuilder::job($job))
            ->with('success', 'Dziękujemy! Zgłoszenie zostało prawidłowo wysłane.');
    }
}
