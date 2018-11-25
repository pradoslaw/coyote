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

        $I->click('Powrót do ogłoszenia');

        $I->seeCurrentRouteIs('job.offer');
        $I->see($title, '.media-heading');
        $I->see($city);
        $I->see($salaryFrom, '.salary');
        $I->see('₣', '.salary');
    }

    public function createJobOfferAsFirm(FunctionalTester $I)
    {
        $I->wantTo('Create new job offer as a firm');
        $I->amOnRoute('job.submit');

        $fake = Factory::create();
        $plan = $I->grabRecord(\Coyote\Plan::class, ['is_default' => 1]);

        $I->seeInField('plan_id', $plan->id);
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
        $I->fillField('youtube_url', 'https://www.youtube.com/watch?v=fz2OUoJpR7k');

        $I->click('Podgląd');
        $I->seeInSource('https://www.youtube.com/embed/fz2OUoJpR7k');
        $I->click('Opublikuj');

        $I->click('Powrót do ogłoszenia');

        $I->seeCurrentRouteIs('job.offer');
        $I->see($title, '.media-heading');
        $I->see($firm, '.employer');
        $I->see($website);
        $I->see($headline, 'blockquote');

        $I->canSeeRecord('firms', ['name' => $firm, 'country_id' => 14, 'city' => 'Wrocław']);
    }

    public function createSecondJobOfferAsFirm(FunctionalTester $I)
    {
        $I->wantTo('Create new job offer when firm exists');

        $plan = $I->grabRecord(\Coyote\Plan::class, ['is_default' => 1]);

        $fake = Factory::create();
        $I->haveRecord('firms', ['user_id' => $this->user->id, 'name' => $firm = $fake->company]);

        $I->amOnRoute('job.submit');

        $I->seeInField('plan_id', $plan->id);
        $I->canSee("Zapisz jako $firm", '.btn-save');
        $I->fillField('input[name=title]', $title = $fake->text(50));
        $I->selectOption(['name' => 'employment_id'], '1');
        $I->fillField('input[name=done]', 1);

        $I->click("Zapisz jako $firm", '.btn-save');

        $I->click('Powrót do ogłoszenia');

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
        $I->click('Powrót do ogłoszenia');

        $I->seeCurrentRouteIs('job.offer');

        $I->see($title, '.media-heading');
        $I->see('New firm', '.employer');
        $I->see($website);
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
        $I->click('Powrót do ogłoszenia');

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
        $I->click('Powrót do ogłoszenia');

        $I->seeCurrentRouteIs('job.offer');

        $I->see($title, '.media-heading');
        $I->see($firm, '.employer');
    }

    public function createOfferByClickingSaveAsButton(FunctionalTester $I)
    {
        $I->wantTo('Create a offer by clicking "save as" button (quick save)');

        $fake = Factory::create();
        $id = $I->haveRecord('firms', ['user_id' => $this->user->id, 'name' => $firm = $fake->company]);

        $I->haveRecord('firm_benefits', ['firm_id' => $id, 'name' => 'Game-boy']);
        $I->haveRecord('firm_benefits', ['firm_id' => $id, 'name' => 'TV']);

        $I->amOnRoute('job.submit');

        $I->fillField('input[name=title]', $title = $fake->text(50));
        $I->selectOption('employment_id', 1);
        $I->fillField('done', 1);

        $I->click("Zapisz jako $firm");
        $I->click('Powrót do ogłoszenia');

        $I->seeCurrentRouteIs('job.offer');
        $I->see($title, '.media-heading');
        $I->see($firm, '.employer');

        $I->see('Game-boy');
        $I->see('TV');
    }

    public function createPremiumOfferWithoutInvoice(FunctionalTester $I)
    {
        $I->wantTo('Create premium offer without invoice');
        $fake = Factory::create();

        $plan = $I->grabRecord(\Coyote\Plan::class, ['name' => 'Standard']);

        $I->amOnRoute('job.submit');

        $I->fillField('title', $title = $fake->text(50));
        $I->selectOption('employment_id', 1);

        $I->fillField('plan_id', $plan->id);
        $I->click('Informacje o firmie');
        $I->click('Podstawowe informacje');

        $I->seeInField('plan_id', $plan->id);

        $I->click('Informacje o firmie');
        $I->selectOption('is_private', '1');
        $I->fillField('done', 1);
        $I->click('Zapisz i zakończ');

        $I->seeCurrentRouteIs('job.payment');
        $I->see('Płatność poprzez bezpieczne połączenie');
        $I->seeOptionIsSelected('invoice[country_id]', 'PL');
        $I->uncheckOption('enable_invoice');

        $I->fillField('name', 'Jan Kowalski');
        $I->fillField('number', '5555555555554444');
        $I->fillField('cvc', '123');

        $I->click('Zapłać i zapisz');

        $I->seeCurrentRouteIs('job.offer');
        $I->see('Dziękujemy! Płatność została zaksięgowana. Za chwilę dostaniesz potwierdzenie na adres e-mail.');

        $job = $I->grabRecord(\Coyote\Job::class, ['title' => $title, 'is_publish' => 1]);

        $payment = $I->grabRecord(\Coyote\Payment::class, ['job_id' => $job->id]);
        $invoice = $I->grabRecord(\Coyote\Invoice::class, ['id' => $payment->invoice_id]);

        $I->assertEquals(\Coyote\Payment::PAID, $payment->status_id);
        $I->assertNotEmpty($payment->invoice);
        $I->assertEquals(40, $payment->days);
        $I->assertTrue($job->is_publish);

        $I->assertEquals(null, $invoice->country_id);

        $item = $I->grabRecord(\Coyote\Invoice\Item::class, ['invoice_id' => $invoice->id]);
        $I->assertEquals(30, $item->price);
        $I->assertEquals(1, $item->vat_rate);
    }

    public function createPremiumOfferWithInvoice(FunctionalTester $I)
    {
        $I->wantTo('Create premium offer with invoice');
        $fake = Factory::create();

        $plan = $I->grabRecord(\Coyote\Plan::class, ['name' => 'Plus']);

        $I->amOnRoute('job.submit');

        $I->fillField('title', $title = $fake->text(50));
        $I->selectOption('employment_id', 1);

        $I->fillField('plan_id', $plan->id);
        $I->click('Informacje o firmie');
        $I->click('Podstawowe informacje');

        $I->seeInField('plan_id', $plan->id);

        $I->click('Informacje o firmie');
        $I->selectOption('is_private', '1');
        $I->fillField('done', 1);
        $I->click('Zapisz i zakończ');

        $I->seeCurrentRouteIs('job.payment');

        $I->fillField('name', 'Jan Kowalski');
        $I->fillField('number', '5555555555554444');
        $I->fillField('cvc', '123');

        $country = $I->grabRecord(\Coyote\Country::class, ['code' => 'GB']);

        $I->selectOption('invoice[country_id]', $country->id);
        $I->fillField('invoice[vat_id]', '1234567');
        $I->fillField('invoice[name]', $fake->name);
        $I->fillField('invoice[city]', $fake->city);
        $I->fillField('invoice[address]', $fake->address);
        $I->fillField('invoice[postal_code]', $fake->postcode);

        $I->click('Zapłać i zapisz');

        $I->seeCurrentRouteIs('job.offer');
        $I->see('Dziękujemy! Płatność została zaksięgowana. Za chwilę dostaniesz potwierdzenie na adres e-mail.');

        /** @var \Coyote\Job $job */
        $job = $I->grabRecord(\Coyote\Job::class, ['title' => $title, 'is_publish' => 1, 'is_ads' => 1]);
        /** @var \Coyote\Payment $payment */
        $payment = $I->grabRecord(\Coyote\Payment::class, ['job_id' => $job->id]);
        /** @var \Coyote\Invoice $invoice */
        $invoice = $I->grabRecord(\Coyote\Invoice::class, ['id' => $payment->invoice_id]);

        $I->assertEquals(\Coyote\Payment::PAID, $payment->status_id);
        $I->assertNotEmpty($payment->invoice);
        $I->assertEquals(40, $payment->days);

        /** @var \Coyote\Invoice\Item $item */
        $item = $I->grabRecord(\Coyote\Invoice\Item::class, ['invoice_id' => $invoice->id]);
        $I->assertEquals(57, $item->price);
        $I->assertEquals(1, $item->vat_rate);
    }

    public function createOfferWithDiscountCoupon(FunctionalTester $I)
    {
        $I->wantTo('Create offer with discount coupon');
        $fake = Factory::create();

        $plan = $I->grabRecord(\Coyote\Plan::class, ['name' => 'Plus']);
        $coupon = $I->haveRecord(\Coyote\Coupon::class, ['code' => $fake->randomAscii, 'amount' => 30]);

        $I->amOnRoute('job.submit');

        $I->fillField('title', $title = $fake->text(50));
        $I->selectOption('employment_id', 1);

        $I->fillField('plan_id', $plan->id);

        $I->click('Informacje o firmie');
        $I->selectOption('is_private', '1');
        $I->fillField('done', 1);
        $I->click('Zapisz i zakończ');

        $I->seeCurrentRouteIs('job.payment');

        $I->seeOptionIsSelected('invoice[country_id]', 'PL');
        $I->uncheckOption('enable_invoice');

        $I->fillField('name', 'Jan Kowalski');
        $I->fillField('number', '5555555555554444');
        $I->fillField('cvc', '123');
        $I->fillField('coupon', $coupon->code);
        $I->fillField('price', 26.7);

        $I->click('Zapłać i zapisz');

        $I->seeCurrentRouteIs('job.offer');

        /** @var \Coyote\Job $job */
        $job = $I->grabRecord(\Coyote\Job::class, ['title' => $title, 'is_publish' => 1, 'is_ads' => 1]);
        /** @var \Coyote\Payment $payment */
        $payment = $I->grabRecord(\Coyote\Payment::class, ['job_id' => $job->id]);

        $I->assertEquals(27, $payment->invoice->netPrice());
        $I->assertEquals($coupon->id, $payment->coupon_id);

        $I->assertNotNull($I->grabRecord('coupons', ['code' => $coupon->code])['deleted_at']);
    }

    public function createOfferWithFullDiscountCoupon(FunctionalTester $I)
    {
        $I->wantTo('Create offer with full discount coupon');
        $fake = Factory::create();

        $plan = $I->grabRecord(\Coyote\Plan::class, ['name' => 'Premium']);
        $coupon = $I->haveRecord(\Coyote\Coupon::class, ['code' => $fake->randomAscii, 'amount' => 200]);

        $I->amOnRoute('job.submit');

        $I->fillField('title', $title = $fake->text(50));
        $I->selectOption('employment_id', 1);

        $I->fillField('plan_id', $plan->id);

        $I->click('Informacje o firmie');
        $I->selectOption('is_private', '1');
        $I->fillField('done', 1);
        $I->click('Zapisz i zakończ');

        $I->seeCurrentRouteIs('job.payment');

        // normalnie caly formularz faktury jest ukrywany przez vue.js po tym, jak cena == 0 zl
        $I->uncheckOption('enable_invoice');
        $I->fillField('coupon', $coupon->code);
        $I->fillField('price', 0);

        $I->click('Zapisz i zakończ');

        $I->seeCurrentRouteIs('job.offer');

        /** @var \Coyote\Job $job */
        $job = $I->grabRecord(\Coyote\Job::class, ['title' => $title, 'is_publish' => 1, 'is_ads' => 1, 'is_highlight' => 1, 'is_on_top' => 1]);
        /** @var \Coyote\Payment $payment */
        $payment = $I->grabRecord(\Coyote\Payment::class, ['job_id' => $job->id]);

        $I->assertEquals(0, $payment->invoice->netPrice());
    }

    public function validatePaymentForm(FunctionalTester $I)
    {
        $I->wantTo('Validate payment form');
        $fake = Factory::create();

        $plan = $I->grabRecord(\Coyote\Plan::class, ['name' => 'Plus']);

        \Coyote\Job::unguard();

        $job = $I->haveRecord(\Coyote\Job::class, [
            'title' => $fake->text(50),
            'user_id' => $this->user->id,
            'description' => $fake->text,
            'deadline_at' => \Carbon\Carbon::now()->addDays(5)
        ]);

        $payment = $I->haveRecord(
            \Coyote\Payment::class,
            ['job_id' => $job->id, 'plan_id' => $plan->id, 'status_id' => \Coyote\Payment::NEW, 'days' => 40]
        );

        $I->amOnRoute('job.payment', [$payment->id]);

        $I->fillField('price', $plan->price);
        $I->click('Zapłać i zapisz');

        $I->seeFormErrorMessage('name');
        $I->seeFormErrorMessage('number');
        $I->seeFormErrorMessage('cvc');
        $I->seeFormErrorMessage('invoice.address');
        $I->seeFormErrorMessage('invoice.postal_code');
        $I->seeFormErrorMessage('invoice.city');

        $I->fillField('name', $fake->firstName . ' ' . $fake->lastName);
        $I->fillField('number', '1111111111111111');
        $I->fillField('cvc', '012');

        $I->uncheckOption('enable_invoice');

        $I->click('Zapłać i zapisz');

        $I->seeFormErrorMessage('number', 'Wprowadzony numer karty jest nieprawidłowy.');
        $I->seeFormErrorMessage('cvc', 'Wprowadzony kod CVC jest nieprawidłowy.');

        $I->fillField('number', '4111111111111111');
        $I->click('Zapłać i zapisz');

        $I->seeCurrentRouteIs('job.offer');
        $I->see('Dziękujemy! Płatność została zaksięgowana. Za chwilę dostaniesz potwierdzenie na adres e-mail.');
    }
}
