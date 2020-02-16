<?php

namespace Tests\Browser;

use Coyote\Country;
use Coyote\Currency;
use Coyote\Firm;
use Coyote\Job;
use Coyote\Payment;
use Coyote\Plan;
use Coyote\User;
use Laravel\Dusk\Browser;
use function PHPSTORM_META\type;
use Tests\DuskTestCase;
use Faker\Factory;

class JobPostingTest extends DuskTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::first();
    }

    public function testCreateJobOfferAsRegularUser()
    {
        $fake = Factory::create();

        $this->browse(function (Browser $browser) use ($fake) {
            $browser->loginAs(User::first());

            $browser->visit('/Praca/Submit')
                ->resize(1920, 1080)
                ->type('title', $title = $fake->text(50))
                ->type('salary_from', $salaryFrom = $fake->numberBetween(0, 999))
                ->type('salary_to', $salaryTo = $fake->numberBetween(1000, 2000))
                ->select('currency_id', Currency::CHF)
                ->type('email', $fake->email)
                ->assertDontSee('Zapisz i zakończ')
                ->press('Informacje o firmie')
                ->waitForLocation('/Praca/Submit/Firm')
                ->click('label[for="is_private_1"]')
                ->scrollTo('#footer-top')
                ->press('Podgląd')
                ->waitForLocation('/Praca/Submit/Preview')
                ->press('Opublikuj')
                ->waitForText('Powrót do ogłoszenia')
                ->clickLink('Powrót do ogłoszenia')
                ->assertSeeIn('.media-heading', $title)
                ->assertSeeIn('.salary', $salaryFrom)
                ->assertSeeIn('.salary', '₣');
        });
    }

    public function testCreateJobOfferAsFirm()
    {
        $fake = Factory::create();

        $this->browse(function (Browser $browser) use ($fake) {
            $browser->loginAs(User::first());

            $plan = Plan::where('name', 'Premium')->first();

            $browser->visit('/Praca/Submit')
                ->resize(1920, 1080)
                ->type('title', $title = $fake->text(50))
                ->value('input[name=plan_id]', $plan->id)
//                ->select('employment_id', 1)
                ->type('email', $fake->email)
                ->press('Informacje o firmie')
                ->waitForLocation('/Praca/Submit/Firm')
                ->assertRadioSelected('is_private', 0)
                ->click('label[for="is_agency_0"]')
                ->assertSee('Benefity')
                ->type('name', $firm = $fake->name)
                ->type('website', $website = 'http://4programmers.net')
                ->select('employees', 2)
                ->value('input[name=country]', 'Polska')
                ->value('input[name=city]', 'Wrocław')
                ->assertValue('input[name=country]', 'Polska')
                ->type('youtube_url', 'https://www.youtube.com/watch?v=fz2OUoJpR7k')
                ->scrollTo('#footer-top')
                ->press('Podgląd')
                ->waitForLocation('/Praca/Submit/Preview')
                ->press('Opublikuj')
                ->waitForText('Powrót do ogłoszenia')
                ->clickLink('Powrót do ogłoszenia')
                ->assertSeeIn('.media-heading', $title)
                ->assertSeeIn('.employer', $firm)
                ->assertSee($website);

//            $this->assertDatabaseHas('firms', ['name' => $firm, 'country_id' => 14, 'city' => 'Wrocław']);
            $this->assertDatabaseHas('firms', ['name' => $firm, 'youtube_url' => 'https://www.youtube.com/embed/fz2OUoJpR7k']);
        });
    }

    public function testCreateJobOfferAsSecondFirm()
    {
        $user = factory(User::class)->create();
        $firm = factory(Firm::class)->create(['user_id' => $user->id]);

        $this->browse(function (Browser $browser) use ($user, $firm) {
            $browser->loginAs($user);

            $fake = Factory::create();

            $browser->visit('/Praca/Submit')
                ->resize(1920, 1080)
                ->type('title', $title = $fake->text(50))
//                ->select('employment_id', 1)
                ->type('email', $fake->email)
                ->press('Informacje o firmie')
                ->waitForLocation('/Praca/Submit/Firm')
                ->assertInputValue('name', $firm->name)
                ->value('input[name=id]', '')
                ->type('website', $website = 'http://4programmers.net')
                ->type('name', 'New firm')
                ->scrollTo('#footer-top')
                ->press('Podgląd')
                ->waitForLocation('/Praca/Submit/Preview')
                ->assertSeeIn('.employer', 'New firm')
                ->press('Opublikuj')
                ->waitForText('Powrót do ogłoszenia')
                ->clickLink('Powrót do ogłoszenia')
                ->assertSeeIn('.media-heading', $title)
                ->assertSeeIn('.employer', 'New firm')
                ->assertSee($website);
        });
    }

    public function testCreatePrivateJobOfferDespiteHavingFirm()
    {
        $user = factory(User::class)->create();
        $firm = factory(Firm::class)->create(['user_id' => $user->id]);

        $this->browse(function (Browser $browser) use ($user, $firm) {
            $browser->loginAs($user);

            $fake = Factory::create();

            $browser->visit('/Praca/Submit')
                ->resize(1920, 1080)
                ->type('title', $title = $fake->text(50))
//                ->select('employment_id', 1)
                ->type('email', $fake->email)
                ->press('Informacje o firmie')
                ->waitForLocation('/Praca/Submit/Firm')
                ->click('label[for="is_private_1"]')
                ->press('Zapisz i zakończ')
                ->waitForText('Powrót do ogłoszenia')
                ->clickLink('Powrót do ogłoszenia')
                ->assertDontSee($firm->name);
        });
    }

    public function testCreateJobOfferWithErrors()
    {
        $user = factory(User::class)->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user);
            $fake = Factory::create();

            $browser->visit('/Praca/Submit')
                ->resize(1920, 1080)
                ->assertInputValue('email', $user->email)
                ->press('Informacje o firmie')
                ->waitForText('Formularz zawiera błędy. Sprawdź poprawność wprowadzonych danych i spróbuj ponownie.')
                ->assertSee('Tytuł jest wymagany.')
                ->type('title', $fake->title)
                ->press('Informacje o firmie')
                ->waitForLocation('/Praca/Submit/Firm')
                ->press('Zapisz i zakończ')
                ->waitForText('Nazwa firmy jest wymagana.');
        });
    }

    public function testQuickCreateJobOffer()
    {
        $user = factory(User::class)->create();
        /** @var Firm $firm */
        $firm = factory(Firm::class)->create(['user_id' => $user->id]);

        $firm->benefits()->create(['name' => 'Game-boy']);
        $firm->benefits()->create(['name' => 'TV']);

        $this->browse(function (Browser $browser) use ($user, $firm) {
            $browser->loginAs($user);
            $fake = Factory::create();

            $browser->visit('/Praca/Submit')
                ->resize(1920, 1080)
                ->type('title', $title = $fake->title)
                ->scrollTo('#footer-top')
                ->press("Zapisz jako $firm->name")
                ->waitForText('Powrót do ogłoszenia')
                ->clickLink('Powrót do ogłoszenia')
                ->assertSeeIn('.media-heading', $title);
        });
    }

    public function testCreatePremiumOfferWithoutInvoice()
    {
        $user = factory(User::class)->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user);
            $fake = Factory::create();

            $browser->visit('/Praca/Submit')
                ->resize(1920, 1080)
                ->type('title', $title = $fake->title)
                ->press('Wybierz')
                ->press('Informacje o firmie')
                ->waitForLocation('/Praca/Submit/Firm')
                ->click('label[for="is_private_1"]')
                ->press('Zapisz i zakończ')
                ->waitForText('Płatność poprzez bezpieczne połączenie')
                ->assertSelected('invoice[country_id]', Country::where('name', 'Polska')->value('id'))
                ->uncheck('enable_invoice')
                ->type('number', '4012001038443335')
                ->type('name', 'Jan Kowalski')
                ->type('cvc', '123')
                ->press('Zapłać i zapisz')
                ->assertSee('Dziękujemy! Płatność została zaksięgowana. Za chwilę dostaniesz potwierdzenie na adres e-mail.');

            /** @var Job $job */
            $job = Job::where('title', $title)->where('is_publish', 1)->first();
            $payment = $job->payments()->first();

            $this->assertEquals($title, $job->title);
            $this->assertEquals(Payment::PAID, $payment->status_id);
            $this->assertEquals(40, $payment->days);
            $this->assertTrue($job->is_publish);
            $this->assertNull($payment->invoice->country_id);
            $this->assertEquals(30, $payment->invoice->items()->first()->price);
        });
    }
}
