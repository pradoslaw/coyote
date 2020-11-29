<?php

namespace Tests\Browser;

use Coyote\Country;
use Coyote\Coupon;
use Coyote\Firm;
use Coyote\Job;
use Faker\Factory;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PaymentTest extends DuskTestCase
{
    /**
     * @var Job
     */
    private $job;

    public function setUp(): void
    {
        parent::setUp();

        $this->job = factory(Job::class)->create(['is_publish' => false]);
    }

    public function testShowVatIdInPaymentForm()
    {
        $faker = Factory::create();

        $country = Country::first();
        $firm = factory(Firm::class)->create(['vat_id' => '123123123', 'country_id' => $country->id, 'user_id' => $this->job->user_id]);

        $this->job->firm_id = $firm->id;
        $this->job->save();

        $payment = $this->job->getUnpaidPayment();
        $coupon = Coupon::create(['amount' => $payment->plan->gross_price, 'code' => $coupon = $faker->text(10)]);

        $this->browse(function (Browser $browser) use ($payment, $firm, $coupon) {
            $browser
                ->resize(1600, 1200)
                ->loginAs($this->job->user)
                ->visitRoute('job.payment', [$payment])
                ->waitFor('#js-payment')
                ->assertInputValue('invoice[vat_id]', $firm->vat_id)
                ->assertSelected('invoice[country_id]', $firm->country_id)
                ->clickLink('Masz kupon rabatowy?')
                ->typeSlowly('coupon', $coupon->code)
                ->waitForText('Płatność nie jest wymagana')
                ->press('Zapisz i zakończ')
                ->waitForText('Dziękujemy! Płatność została zaksięgowana. Za chwilę dostaniesz potwierdzenie na adres e-mail.')
                ->assertRouteIs('job.offer', [$this->job->id, $this->job->slug]);
        });

        $this->job->refresh();

        $this->assertTrue($this->job->is_publish);
        $this->assertEmpty($this->job->getUnpaidPayment());
    }

    public function testSubmitFormWithoutPaymentNeeded()
    {
        try {
            $this->job->plan->discount = 1;
            $this->job->plan->save();

            $payment = $this->job->getUnpaidPayment();

            $this->browse(function (Browser $browser) use ($payment) {
                $browser
                    ->resize(1600, 1200)
                    ->loginAs($this->job->user)
                    ->visitRoute('job.payment', [$payment])
                    ->waitForText('Płatność nie jest wymagana')
                    ->press('Zapisz i zakończ')
                    ->waitForText('Dziękujemy! Płatność została zaksięgowana. Za chwilę dostaniesz potwierdzenie na adres e-mail.')
                    ->assertRouteIs('job.offer', [$this->job->id, $this->job->slug]);
            });
        } finally {
            $this->job->plan->discount = 1;
            $this->job->plan->save();
        }
    }
}
