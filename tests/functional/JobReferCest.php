<?php

use Faker\Factory;

class JobReferCest
{
    private $user;
    private $job;

    public function _before(FunctionalTester $I)
    {
        $this->user = $I->createUser();

        \Coyote\Job::unguard();

        $fake = Factory::create();

        $this->job = $I->haveRecord(\Coyote\Job::class, [
            'title' => $fake->title,
            'description' => $fake->text(),
            'user_id' => $this->user->id,
            'deadline_at' => \Carbon\Carbon::now()->addDay(1),
            'email' => $fake->email
        ]);

        \Coyote\Job::reguard();
    }

    public function tryToSubmitApplicationWithErrors(FunctionalTester $I)
    {
        $I->amOnRoute('job.refer', [$this->job->id]);
        $I->see('E-mail kandydata');

        $I->submitForm('.card-body form', [ ]);

        $I->seeFormErrorMessage('name');
        $I->seeFormErrorMessage('email');
        $I->seeFormErrorMessage('friend_name');
        $I->seeFormErrorMessage('friend_email');
    }

    public function submitApplication(FunctionalTester $I)
    {
        $I->wantTo('Submit job refer form');

        $fake = Factory::create();

        $I->amOnRoute('job.refer', [$this->job->id]);

        $I->submitForm('.card-body form', [
            'name' => $name = $fake->name,
            'email' => $email = $fake->email,
            'friend_name' => $friendName = $fake->name,
            'friend_email' => $friendEmail = $fake->email
        ]);

        $I->seeCurrentRouteIs('job.offer');
        $I->see('Dziękujemy! Zgłoszenie zostało prawidłowo wysłane.');

        $I->seeRecord('job_refers', ['name' => $name, 'email' => $email, 'friend_name' => $friendName, 'friend_email' => $friendEmail]);
    }
}
