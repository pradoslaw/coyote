<?php

use Faker\Factory;

class JobPostingCest
{
    private $user;

    public function _before(FunctionalTester $I)
    {
        $this->user = $I->createUser();
        $I->amLoggedAs($this->user);

        \Coyote\Job::reguard();
    }

    public function _after(FunctionalTester $I)
    {
    }

    // tests
    public function createJobOfferAsRegularUser(FunctionalTester $I)
    {
        $I->wantTo('Crate job offer as regular user');
        $I->amOnRoute('job.home');
        $I->click('Dodaj ofertę pracy');

        $I->canSeeCurrentRouteIs('job.submit');

        $fake = Factory::create();

        $I->fillField('input[name=title]', $title = $fake->text(50));
        $I->fillField('input[name=city]', $city = 'Zielona góra');
        $I->fillField('input[name=salary_from]', $salaryFrom = $fake->numberBetween(0, 999));
        $I->fillField('input[name=salary_to]', $salaryTo = $fake->numberBetween(1000, 2000));
        $I->selectOption('currency_id', 5);

        $I->fillField('textarea[name=description]', $fake->text);
        $I->selectOption(['name' => 'employment_id'], '1');
        $I->cantSee('Zapisz i zakończ');

        $I->click('Informacje o firmie');
        $I->seeCurrentRouteIs('job.submit.firm');

        $I->selectOption('input[name=is_private]', '1');
        $I->click('Podgląd');
        $I->click('Opublikuj');

        $I->seeCurrentRouteIs('job.offer');
        $I->see($title, '.media-heading');
        $I->see($city);
        $I->see($salaryFrom, '.salary');
        $I->see('CHF', '.salary');
    }

    public function createJobOfferAsFirm(FunctionalTester $I)
    {
        $I->wantTo('Create new job offer as a firm');
        $I->amOnRoute('job.submit');

        $fake = Factory::create();

        $I->fillField('input[name=title]', $title = $fake->text(50));
        $I->selectOption(['name' => 'employment_id'], '1');
        $I->cantSee('Zapisz jako');

        $I->click('Informacje o firmie');
        $I->seeCurrentRouteIs('job.submit.firm');

        $I->seeOptionIsSelected('input[name=is_private]', '0');

        $I->fillField(['name' => 'name'], $firm = $fake->name);
        $I->fillField(['name' => 'website'], $website = 'http://4programmers.net');
        $I->fillField(['name' => 'headline'], $headline = $fake->text(20));
        $I->fillField('textarea[name=description]', $fake->text());
        $I->selectOption('select[name=employees]', 2);
        $I->fillField('country', 'Polska');
        $I->fillField('city', 'Wrocław');

        $I->click('Podgląd');
        $I->click('Opublikuj');

        $I->seeCurrentRouteIs('job.offer');
        $I->see($title, '.media-heading');
        $I->see($firm, '.employer');
        $I->see($website, '#box-job-firm');
        $I->see($headline, 'blockquote');

        $I->canSeeRecord('firms', ['name' => $firm, 'country_id' => 14, 'city' => 'Wrocław']);
    }

    public function createSecondJobOfferAsFirm(FunctionalTester $I)
    {
        $I->wantTo('Create new job offer when firm exists');

        $fake = Factory::create();
        $I->haveRecord('firms', ['user_id' => $this->user->id, 'name' => $firm = $fake->company]);

        $I->amOnRoute('job.submit');
        $I->canSee("Zapisz jako $firm", '.btn-save');
        $I->fillField('input[name=title]', $title = $fake->text(50));
        $I->selectOption(['name' => 'employment_id'], '1');
        $I->fillField('input[name=done]', 1);

        $I->click("Zapisz jako $firm", '.btn-save');

        $I->seeCurrentRouteIs('job.offer');
        $I->see($title, '.media-heading');
        $I->see($firm, '.employer');
    }

    public function createJobOfferWithSecondFirm(FunctionalTester $I)
    {
        $I->wantTo('Create new job offer and new firm');

        $fake = Factory::create();
        $I->haveRecord('firms', ['user_id' => $this->user->id, 'name' => $firm = $fake->company]);

        $I->amOnRoute('job.submit');

        $I->fillField('input[name=title]', $title = $fake->text(50));
        $I->selectOption(['name' => 'employment_id'], '1');

        $I->click('Informacje o firmie');
        $I->seeCurrentRouteIs('job.submit.firm');

        $I->canSeeInField('input[name=name]', $firm);
        $I->fillField('input[name=id]', '');
        $I->fillField(['name' => 'website'], $website = 'http://4programmers.net');
        $I->fillField(['name' => 'headline'], $headline = $fake->text(20));
        $I->fillField('input[name=name]', 'New firm');

        $I->click('Podgląd');
        $I->see($title, '.media-heading');
        $I->see('New firm', '.employer');
        $I->see($headline, 'blockquote');

        $I->click('Opublikuj');
        $I->seeCurrentRouteIs('job.offer');

        $I->see($title, '.media-heading');
        $I->see('New firm', '.employer');
        $I->see($website, '#box-job-firm');
        $I->see($headline, 'blockquote');
    }

    public function createPrivateJobOfferDespiteHavingFirm(FunctionalTester $I)
    {
        $I->wantTo('Create a private job offer despite having a firm');

        $fake = Factory::create();
        $I->haveRecord('firms', ['user_id' => $this->user->id, 'name' => $firm = $fake->company]);

        $I->amOnRoute('job.submit');

        $I->fillField('input[name=title]', $title = $fake->text(50));
        $I->selectOption(['name' => 'employment_id'], '1');

        $I->click('Informacje o firmie');
        $I->seeCurrentRouteIs('job.submit.firm');

        $I->seeOptionIsSelected('input[name=is_private]', '0');
        $I->selectOption('input[name=is_private]', '1');
        $I->fillField('input[name=done]', 1);

        $I->click('Zapisz i zakończ');

        $I->seeCurrentRouteIs('job.offer');
        $I->see($title, '.media-heading');
        $I->cantSee($firm);
    }

    public function tryToCreateJobOfferWithErrors(FunctionalTester $I)
    {
        $I->wantTo('Create job offer with empty fields');
        $fake = Factory::create();

        $I->amOnRoute('job.submit');

        $I->seeOptionIsSelected('country_id', 'Polska');

        $I->fillField('title', $title = $fake->text(50));
        $I->fillField('deadline', 100);
        $I->selectOption('employment_id', 2);
        $I->selectOption('country_id', 2);
        $I->selectOption('rate_id', 2);
        $I->selectOption('remote_range', 60);

        $I->fillField('email', '');
        $I->click('Informacje o firmie');

        $I->canSeeFormHasErrors();

        $I->canSeeOptionIsSelected('country_id', 'Belgia');
        $I->canSeeOptionIsSelected('employment_id', 'Umowa zlecenie');
        $I->canSeeOptionIsSelected('rate_id', 'rocznie');
        $I->canSeeOptionIsSelected('remote_range', '60%');
        $I->seeInField('title', $title);
        $I->seeInField('email', '');
        $I->seeInField('deadline', 100);

        $I->fillField('email', $email = $fake->email);
        $I->click('Informacje o firmie');
        $I->click('Podstawowe informacje');

        $I->canSeeOptionIsSelected('country_id', 'Belgia');
        $I->canSeeOptionIsSelected('employment_id', 'Umowa zlecenie');
        $I->canSeeOptionIsSelected('rate_id', 'rocznie');
        $I->canSeeOptionIsSelected('remote_range', '60%');
        $I->seeInField('title', $title);
        $I->seeInField('email', $email);
    }

    public function tryToCreateJobOfferWithEmptyFirmName(FunctionalTester $I)
    {
        $I->wantTo('Create job offer with empty firm name');
        $fake = Factory::create();

        $I->amOnRoute('job.submit');

        $I->fillField('title', $title = $fake->text(50));
        $I->selectOption('employment_id', 1);

        $I->click('Informacje o firmie');
        $I->seeCurrentRouteIs('job.submit.firm');

        $I->click('Podgląd');
        $I->canSeeFormHasErrors();
        $I->canSeeFormErrorMessage('name', 'Nazwa firmy jest wymagana.');
        $I->fillField('name', $firm = $fake->company);

        $I->click('Podgląd');
        $I->see($title, '.media-heading');
        $I->see($firm, '.employer');
        $I->click('Opublikuj');

        $I->seeCurrentRouteIs('job.offer');

        $I->see($title, '.media-heading');
        $I->see($firm, '.employer');
    }
}
