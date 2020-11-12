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

    public function testSubmitWithoutFirm()
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
                ->press('Zapisz')
                ->waitForText('Powrót do ogłoszenia')
                ->clickLink('Powrót do ogłoszenia')
                ->assertSeeIn('.media-heading', $title)
                ->assertSeeIn('.salary', $salaryFrom)
                ->assertSeeIn('.salary', '₣');
        });
    }

    public function testSubmitWithFirm()
    {
        $fake = Factory::create();

        $this->browse(function (Browser $browser) use ($fake) {
            $browser->loginAs(User::first());

            $browser->visit('/Praca/Submit')
                ->resize(1920, 1080)
                ->type('title', $title = $fake->text(50))
                ->type('email', $fake->email)
                ->press('Informacje o firmie')
//                ->click('label[for="is_agency_0"]')
                ->assertSee('Benefity')
                ->type('firm[name]', $firm = $fake->name)
                ->type('firm[website]', $website = 'http://4programmers.net')
                ->select('firm[employees]', 2)
                ->type('address', 'Wrocław Rynek')
                ->keys('input[name="address"]', '{enter}')
                ->pause(1000)
                ->screenshot('t1')
                ->type('firm[youtube_url]', 'https://www.youtube.com/watch?v=fz2OUoJpR7k')
                ->scrollTo('#footer-top')
                ->press('Zapisz')
                ->waitForText('Powrót do ogłoszenia')
                ->clickLink('Powrót do ogłoszenia')
                ->assertSeeIn('.media-heading', $title)
                ->assertSeeIn('.employer', $firm)
                ->assertSee($website);

            $this->assertDatabaseHas('firms', ['name' => $firm, 'city' => 'Wrocław']);
            $this->assertDatabaseHas('firms', ['name' => $firm, 'youtube_url' => 'https://www.youtube.com/embed/fz2OUoJpR7k']);
        });
    }

    public function testSubmitOfferWithSecondFirm()
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
                ->assertInputValue('firm[name]', $firm->name)
                ->scrollTo('#js-submit-form')
                ->clickAtXPath('/html/body/div[1]/div[2]/div/main/div[3]/div[3]/div[2]/div[2]/div/div/a')
                ->assertInputValue('firm[name]', '')
                ->type('firm[website]', $website = 'http://4programmers.net')
                ->type('firm[name]', 'New firm')
                ->press('Zapisz jako New firm')
                ->waitForText('Powrót do ogłoszenia')
                ->clickLink('Powrót do ogłoszenia')
                ->assertSeeIn('.media-heading', $title)
                ->assertSeeIn('.employer', 'New firm')
                ->assertSee($website);
        });
    }

    public function testSubmitPrivateJobOfferDespiteHavingFirm()
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
                ->scrollTo('#footer-top')
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
                ->scrollTo('#footer-top')
                ->press('Informacje o firmie')
                ->waitForText('Formularz zawiera błędy. Sprawdź poprawność wprowadzonych danych i spróbuj ponownie.')
                ->assertSee('Tytuł jest wymagany.')
                ->type('title', $fake->title)
                ->scrollTo('#footer-top')
                ->press('Informacje o firmie')
                ->waitForLocation('/Praca/Submit/Firm')
                ->scrollTo('#footer-top')
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
                ->scrollTo('#footer-top')
                ->press('Informacje o firmie')
                ->waitForLocation('/Praca/Submit/Firm')
                ->click('label[for="is_private_1"]')
                ->press('Zapisz i zakończ')
                ->waitForText('Płatność poprzez bezpieczne połączenie')
                ->click('label[for="enable-invoice"]')
                ->type('number', '4012001038443335')
                ->type('name', 'Jan Kowalski')
                ->type('cvc', '123')
                ->type('exp', '12/24')
                ->press('Zapłać i zapisz')
                ->waitForText('Dziękujemy! Płatność została zaksięgowana. Za chwilę dostaniesz potwierdzenie na adres e-mail.');

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
