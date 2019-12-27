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

//    public function createPremiumOfferWithInvoice(FunctionalTester $I)
//    {
//        $I->wantTo('Create premium offer with invoice');
//        $fake = Factory::create();
//
//        $plan = $I->grabRecord(\Coyote\Plan::class, ['name' => 'Plus']);
//
//        $I->amOnRoute('job.submit');
//
//        $I->fillField('title', $title = $fake->text(50));
//        $I->selectOption('employment_id', 1);
//
//        $I->fillField('plan_id', $plan->id);
//        $I->click('Informacje o firmie');
//        $I->click('Podstawowe informacje');
//
//        $I->seeInField('plan_id', $plan->id);
//
//        $I->click('Informacje o firmie');
//        $I->selectOption('is_private', '1');
//        $I->fillField('done', 1);
//        $I->click('Zapisz i zakończ');
//
//        $I->seeCurrentRouteIs('job.payment');
//
//        $I->fillField('name', 'Jan Kowalski');
//        $I->fillField('number', '4012001038443335');
//        $I->fillField('cvc', '123');
//
//        $country = $I->grabRecord(\Coyote\Country::class, ['code' => 'GB']);
//
//        $I->selectOption('invoice[country_id]', $country->id);
//        $I->fillField('invoice[vat_id]', '1234567');
//        $I->fillField('invoice[name]', $fake->name);
//        $I->fillField('invoice[city]', $fake->city);
//        $I->fillField('invoice[address]', $fake->address);
//        $I->fillField('invoice[postal_code]', $fake->postcode);
//
//        $I->click('Zapłać i zapisz');
//
//        $I->seeCurrentRouteIs('job.offer');
//        $I->see('Dziękujemy! Płatność została zaksięgowana. Za chwilę dostaniesz potwierdzenie na adres e-mail.');
//
//        /** @var \Coyote\Job $job */
//        $job = $I->grabRecord(\Coyote\Job::class, ['title' => $title, 'is_publish' => 1, 'is_ads' => 1]);
//        /** @var \Coyote\Payment $payment */
//        $payment = $I->grabRecord(\Coyote\Payment::class, ['job_id' => $job->id]);
//        /** @var \Coyote\Invoice $invoice */
//        $invoice = $I->grabRecord(\Coyote\Invoice::class, ['id' => $payment->invoice_id]);
//
//        $I->assertEquals(\Coyote\Payment::PAID, $payment->status_id);
//        $I->assertNotEmpty($payment->invoice);
//        $I->assertEquals(40, $payment->days);
//
//        /** @var \Coyote\Invoice\Item $item */
//        $item = $I->grabRecord(\Coyote\Invoice\Item::class, ['invoice_id' => $invoice->id]);
//        $I->assertEquals(57, $item->price);
//        $I->assertEquals(1, $item->vat_rate);
//    }
//
//    public function createOfferWithDiscountCoupon(FunctionalTester $I)
//    {
//        $I->wantTo('Create offer with discount coupon');
//        $fake = Factory::create();
//
//        $plan = $I->grabRecord(\Coyote\Plan::class, ['name' => 'Plus']);
//        $coupon = $I->haveRecord(\Coyote\Coupon::class, ['code' => $fake->randomAscii, 'amount' => 30]);
//
//        $I->amOnRoute('job.submit');
//
//        $I->fillField('title', $title = $fake->text(50));
//        $I->selectOption('employment_id', 1);
//
//        $I->fillField('plan_id', $plan->id);
//
//        $I->click('Informacje o firmie');
//        $I->selectOption('is_private', '1');
//        $I->fillField('done', 1);
//        $I->click('Zapisz i zakończ');
//
//        $I->seeCurrentRouteIs('job.payment');
//
//        $I->seeOptionIsSelected('invoice[country_id]', 'PL');
//        $I->uncheckOption('enable_invoice');
//
//        $I->fillField('name', 'Jan Kowalski');
//        $I->fillField('number', '4012001038443335');
//        $I->fillField('cvc', '123');
//        $I->fillField('coupon', $coupon->code);
//        $I->fillField('price', 26.7);
//
//        $I->click('Zapłać i zapisz');
//
//        $I->seeCurrentRouteIs('job.offer');
//
//        /** @var \Coyote\Job $job */
//        $job = $I->grabRecord(\Coyote\Job::class, ['title' => $title, 'is_publish' => 1, 'is_ads' => 1]);
//        /** @var \Coyote\Payment $payment */
//        $payment = $I->grabRecord(\Coyote\Payment::class, ['job_id' => $job->id]);
//
//        $I->assertEquals(27, $payment->invoice->netPrice());
//        $I->assertEquals($coupon->id, $payment->coupon_id);
//
//        $I->assertNotNull($I->grabRecord('coupons', ['code' => $coupon->code])['deleted_at']);
//    }
//
//    public function createOfferWithFullDiscountCoupon(FunctionalTester $I)
//    {
//        $I->wantTo('Create offer with full discount coupon');
//        $fake = Factory::create();
//
//        $plan = $I->grabRecord(\Coyote\Plan::class, ['name' => 'Premium']);
//        $coupon = $I->haveRecord(\Coyote\Coupon::class, ['code' => $fake->randomAscii, 'amount' => 200]);
//
//        $I->amOnRoute('job.submit');
//
//        $I->fillField('title', $title = $fake->text(50));
//        $I->selectOption('employment_id', 1);
//
//        $I->fillField('plan_id', $plan->id);
//
//        $I->click('Informacje o firmie');
//        $I->selectOption('is_private', '1');
//        $I->fillField('done', 1);
//        $I->click('Zapisz i zakończ');
//
//        $I->seeCurrentRouteIs('job.payment');
//
//        // normalnie caly formularz faktury jest ukrywany przez vue.js po tym, jak cena == 0 zl
//        $I->uncheckOption('enable_invoice');
//        $I->fillField('coupon', $coupon->code);
//        $I->fillField('price', 0);
//
//        $I->click('Zapisz i zakończ');
//
//        $I->seeCurrentRouteIs('job.offer');
//
//        /** @var \Coyote\Job $job */
//        $job = $I->grabRecord(\Coyote\Job::class, ['title' => $title, 'is_publish' => 1, 'is_ads' => 1, 'is_highlight' => 1, 'is_on_top' => 1]);
//        /** @var \Coyote\Payment $payment */
//        $payment = $I->grabRecord(\Coyote\Payment::class, ['job_id' => $job->id]);
//
//        $I->assertEquals(0, $payment->invoice->netPrice());
//    }
//
//    public function validatePaymentForm(FunctionalTester $I)
//    {
//        $I->wantTo('Validate payment form');
//        $fake = Factory::create();
//
//        $plan = $I->grabRecord(\Coyote\Plan::class, ['name' => 'Plus']);
//
//        \Coyote\Job::unguard();
//
//        $job = $I->haveRecord(\Coyote\Job::class, [
//            'title' => $fake->text(50),
//            'user_id' => $this->user->id,
//            'description' => $fake->text,
//            'deadline_at' => \Carbon\Carbon::now()->addDays(5)
//        ]);
//
//        $payment = $I->haveRecord(
//            \Coyote\Payment::class,
//            ['job_id' => $job->id, 'plan_id' => $plan->id, 'status_id' => \Coyote\Payment::NEW, 'days' => 40]
//        );
//
//        $I->amOnRoute('job.payment', [$payment->id]);
//
//        $I->fillField('price', $plan->price);
//        $I->click('Zapłać i zapisz');
//
//        $I->seeFormErrorMessage('name');
//        $I->seeFormErrorMessage('number');
//        $I->seeFormErrorMessage('cvc');
//        $I->seeFormErrorMessage('invoice.address');
//        $I->seeFormErrorMessage('invoice.postal_code');
//        $I->seeFormErrorMessage('invoice.city');
//
//        $I->fillField('name', $fake->firstName . ' ' . $fake->lastName);
//        $I->fillField('number', '1111111111111111');
//        $I->fillField('cvc', '012');
//
//        $I->uncheckOption('enable_invoice');
//
//        $I->click('Zapłać i zapisz');
//
//        $I->seeFormErrorMessage('number', 'Wprowadzony numer karty jest nieprawidłowy.');
//        $I->seeFormErrorMessage('cvc', 'Wprowadzony kod CVC jest nieprawidłowy.');
//
//        $I->fillField('number', '4012001038443335');
//        $I->click('Zapłać i zapisz');
//
//        $I->seeCurrentRouteIs('job.offer');
//        $I->see('Dziękujemy! Płatność została zaksięgowana. Za chwilę dostaniesz potwierdzenie na adres e-mail.');
//    }
}
