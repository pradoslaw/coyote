<?php

namespace Tests\Browser;

use Coyote\Currency;
use Coyote\Firm;
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
                ->select('employment_id', 1)
                ->assertDontSee('Zapisz i zakończ')
                ->press('Informacje o firmie')
                ->waitForLocation('/Praca/Submit/Firm')
                ->radio('is_private', 1)
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

            $plan = Plan::where('is_default', 1)->first();

            $browser->visit('/Praca/Submit')
                ->resize(1920, 1080)
                ->type('title', $title = $fake->text(50))
                ->value('input[name=plan_id]', $plan->id)
                ->select('employment_id', 1)
                ->type('email', $fake->email)
                ->press('Informacje o firmie')
                ->waitForLocation('/Praca/Submit/Firm')
                ->assertRadioSelected('is_private', 0)
                ->radio('is_agency', 0)
                ->assertSee('Benefity')
                ->type('name', $firm = $fake->name)
                ->type('website', $website = 'http://4programmers.net')
                ->select('employees', 2)
                ->value('input[name=country]', 'Polska')
                ->value('input[name=city]', 'Wrocław')
                ->assertValue('input[name=country]', 'Polska')
                ->type('youtube_url', 'https://www.youtube.com/watch?v=fz2OUoJpR7k')
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
                ->select('employment_id', 1)
                ->type('email', $fake->email)
                ->press('Informacje o firmie')
                ->waitForLocation('/Praca/Submit/Firm')
                ->assertInputValue('name', $firm->name)
                ->value('input[name=id]', '')
                ->type('website', $website = 'http://4programmers.net')
                ->type('name', 'New firm')
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
                ->select('employment_id', 1)
                ->type('email', $fake->email)
                ->press('Informacje o firmie')
                ->waitForLocation('/Praca/Submit/Firm')
                ->radio('is_private', 1)
                ->press('Zapisz i zakończ')
                ->waitForText('Powrót do ogłoszenia')
                ->clickLink('Powrót do ogłoszenia')
                ->assertDontSee($firm->name);
        });
    }
}
