<?php

use Faker\Factory;

class JobPostingApplicationCest
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
        $I->amOnRoute('job.application', [$this->job->id]);

        $fake = Factory::create();

        $I->fillField('email', $email = $fake->email);
        $I->selectOption('salary', 'od 1000 zł m-c');
        $I->selectOption('dismissal_period', '3 dni robocze');

        $I->click('Zapisz');

        $I->seeFormErrorMessage('name');

        $I->seeInField('email', $email);
        $I->seeOptionIsSelected('salary', 'od 1000 zł m-c');
        $I->seeOptionIsSelected('dismissal_period', '3 dni robocze');
    }

    public function submitApplication(FunctionalTester $I)
    {
        $I->wantTo('Submit job application form');

        $fake = Factory::create();

        $I->amOnRoute('job.application', [$this->job->id]);

        $I->fillField('email', $fakeEmail = $fake->email);
        $I->fillField('name', $fake->name);
        $I->fillField('phone', $fake->phoneNumber);
        $I->fillField('text', '"Lorem" \'ipsum\'');
        $I->selectOption('salary', 'od 1000 zł m-c');
        $I->selectOption('dismissal_period', '3 dni robocze');
        $I->checkOption('#remember');

        $I->click('Zapisz');

        $I->seeCurrentRouteIs('job.offer');

        \Coyote\Job::unguard();
        $this->job = $I->haveRecord(\Coyote\Job::class, [
            'title' => $fake->title,
            'description' => $fake->text(),
            'user_id' => $this->user->id,
            'deadline_at' => \Carbon\Carbon::now()->addDay(1),
            'email' => $fake->email
        ]);

        $I->amOnRoute('job.application', [$this->job->id]);

        $I->seeInField('email', $fakeEmail);
    }
}
