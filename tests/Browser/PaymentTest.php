<?php

namespace Tests\Browser;

use Coyote\Country;
use Coyote\Coupon;
use Coyote\Firm;
use Coyote\Job;
use Coyote\Payment;
use Coyote\Plan;
use Faker\Factory;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PaymentTest extends DuskTestCase
{
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

    }

    public function testSubmitFormWithBankTransfer()
    {
        $plan = Plan::where('name', 'Plus')->first();

        $job = factory(Job::class)->create(['is_publish' => false, 'plan_id' => $plan->id]);

        $payment = $job->getUnpaidPayment();

        $this->browse(function (Browser $browser) use ($payment, $job) {
            $browser
                ->resize(1600, 1200)
                ->loginAs($job->user)
                ->visitRoute('job.payment', [$payment])
                ->waitFor('#js-payment')
                ->type('invoice[name]', $this->faker->name)
                ->type('invoice[address]', $this->faker->address)
                ->type('invoice[postal_code]', $this->faker->postcode)
                ->type('invoice[city]', $this->faker->city)
                ->select('invoice[country_id]', 12)
                ->type('invoice[vat_id]', '123123123')
                ->clickAtXPath('//*[@id="js-payment"]/form/div[1]/div[2]/ul/li[2]/a')
                ->press('Zapłać i zapisz')
                ->waitForText('P24 test payment page', 30)
                ->press('AUTHORIZE TEST PAYMENT')
                ->waitForText('Dziękujemy! W momencie zaksięgowania wpłaty, dostaniesz potwierdzenie na adres e-mail.')
                ->assertRouteIs('job.offer', [$job->id, $job->slug]);
        });
    }

    public function testShowVatIdInPaymentForm()
    {
        $job = factory(Job::class)->create(['is_publish' => false]);
        $faker = Factory::create();

        $country = Country::first();
        $firm = factory(Firm::class)->create(['vat_id' => '123123123', 'country_id' => $country->id, 'user_id' => $job->user_id]);

        $job->firm_id = $firm->id;
        $job->save();

        $payment = $job->getUnpaidPayment();
        $coupon = Coupon::create(['amount' => $payment->plan->price, 'code' => $coupon = $faker->randomAscii]);

        $this->browse(function (Browser $browser) use ($payment, $firm, $coupon, $job) {
            $browser
                ->resize(1600, 1200)
                ->loginAs($job->user)
                ->visitRoute('job.payment', [$payment])
                ->waitFor('#js-payment')
                ->assertInputValue('invoice[vat_id]', $firm->vat_id)
                ->assertSelected('invoice[country_id]', $firm->country_id)
                ->clickLink('Masz kupon rabatowy?')
                ->typeSlowly('coupon', $coupon->code)
                ->waitForText('Płatność nie jest wymagana')
                ->press('Zapisz i zakończ')
                ->waitForText('Dziękujemy! Płatność została zaksięgowana. Za chwilę dostaniesz potwierdzenie na adres e-mail.')
                ->assertRouteIs('job.offer', [$job->id, $job->slug]);
        });

        $job->refresh();

        $this->assertTrue($job->is_publish);
        $this->assertEmpty($job->getUnpaidPayment());
    }

    public function testSubmitFormWithoutPaymentNeeded()
    {
        $job = factory(Job::class)->create(['is_publish' => false]);

        try {
            $job->plan->discount = 1;
            $job->plan->save();

            $payment = $job->getUnpaidPayment();

            $this->browse(function (Browser $browser) use ($payment, $job) {
                $browser
                    ->resize(1600, 1200)
                    ->loginAs($job->user)
                    ->visitRoute('job.payment', [$payment])
                    ->waitForText('Płatność nie jest wymagana')
                    ->press('Zapisz i zakończ')
                    ->waitForText('Dziękujemy! Płatność została zaksięgowana. Za chwilę dostaniesz potwierdzenie na adres e-mail.', 15)
                    ->assertRouteIs('job.offer', [$job->id, $job->slug]);
            });
        } finally {
            $job->plan->discount = 1;
            $job->plan->save();
        }
    }
}
