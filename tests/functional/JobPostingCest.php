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
        $I->fillField('input[name=city]', $city = $fake->city);
        $I->fillField('input[name=salary_from]', $salaryFrom = $fake->numberBetween(0, 999));
        $I->fillField('input[name=salary_to]', $salaryTo = $fake->numberBetween(1000, 2000));

        $I->fillField('textarea[name=description]', $fake->text);
        $I->selectOption(['name' => 'employment_id'], '1');
        $I->cantSee('Zapisz i zakończ');

        $I->click('Informacje o firmie');
        $I->seeCurrentRouteIs('job.submit.firm');

        $I->selectOption('input[name=private]', '1');
        $I->click('Podgląd');
        $I->click('Opublikuj');

        $I->seeCurrentRouteIs('job.offer');
        $I->see($title, '.media-heading');
        $I->see($city);
        $I->see($salaryFrom, '.salary');
    }

    public function createJobOfferAsFirm(FunctionalTester $I)
    {
        $I->wantTo('Create new job offer as a firm');
        $I->amOnRoute('job.submit');

        $fake = Factory::create();

        $I->fillField('input[name=title]', $title = $fake->text(50));
        $I->selectOption(['name' => 'employment_id'], '1');
        $I->cantSee('Zapisz i zakończ');

        $I->click('Informacje o firmie');
        $I->seeCurrentRouteIs('job.submit.firm');

        $I->selectOption('input[name=private]', '0');

        $I->fillField(['name' => 'name'], $firm = $fake->name);
        $I->fillField(['name' => 'website'], $website = 'http://4programmers.net');
        $I->fillField(['name' => 'headline'], $headline = $fake->text(20));
        $I->fillField('textarea[name=description]', $fake->text());
        $I->selectOption('select[name=employees]', 2);

        $I->click('Podgląd');
        $I->click('Opublikuj');

        $I->seeCurrentRouteIs('job.offer');
        $I->see($title, '.media-heading');
        $I->see($firm, '.employer');
        $I->see($website, '#box-job-firm');
        $I->see($headline, 'blockquote');

    }
}
