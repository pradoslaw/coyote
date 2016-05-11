<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Factories\FilesystemFactory;
use Coyote\Http\Factories\MailFactory;
use Coyote\Http\Forms\Job\ApplicationForm;
use Coyote\Job;

class ApplicationController extends Controller
{
    use FilesystemFactory, MailFactory;

    /**
     * @param Job $job
     * @return $this
     */
    public function submit(Job $job)
    {
        $this->breadcrumb->push($job->title, route('job.offer', [$job->id, $job->slug]));
        $this->breadcrumb->push('Aplikuj na to stanowisko pracy');

        /**
         * @var ApplicationForm $form
         */
        $form = $this->createForm(ApplicationForm::class);

        if ($this->userId) {
            $form->email->setValue(auth()->user()->email);
            // set default message
            $form->message->setValue(view('job.partials.application', compact('job')));
        }

        return $this->view('job.application', compact('job', 'form'))->with(
            'subscribed',
            $this->userId ? $job->subscribers()->forUser($this->userId)->exists() : false
        );
    }

    /**
     * @param Job $job
     * @param ApplicationForm $form
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Job $job, ApplicationForm $form)
    {
        $attachment = [];

        $data = $form->all() + ['user_id' => $this->userId, 'session_id' => $this->sessionId];
        $file = $form->getRequest()->file('cv');

        if ($file !== null) {
            $attachment = [
                'name' => $file->getClientOriginalName(),
                'content' => file_get_contents($file->getRealPath())
            ];
        }

        \DB::transaction(function () use ($job, $data, $attachment) {
            $job->applications()->create($data);
            $job = $job->toArray();

            $this->getMailFactory()->send([], [], function ($mailer) use ($data, $job, $attachment) {
                if (!empty($data['phone'])) {
                    // @todo do zmiany, nie mozemy konstrulowac tagow html w tekscie
                    $data['message'] .= '<br>Nr tel.: ' . $data['phone'];
                }

                $mailer->to($job['email']);
                $mailer->replyTo($data['email'], $data['name']);
                $mailer->subject(sprintf('[%s] %s', $data['name'], $job['title']));
                $mailer->setBody($data['message'], 'text/html');

                if (!empty($attachment)) {
                    $mailer->attachData($attachment['content'], $attachment['name']);
                }

                if (!empty($data['cc'])) {
                    $mailer->cc($data['email']);
                }
            });
        });

        return redirect()
            ->route('job.offer', [$job->id, $job->slug])
            ->with('success', 'Zgłoszenie zostało prawidłowo wysłane.');
    }
}
