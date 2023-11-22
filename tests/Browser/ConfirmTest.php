<?php

namespace Tests\Browser;

use Coyote\User;
use Faker\Factory;
use Laravel\Dusk\Browser;

class ConfirmTest extends DuskTestCase
{
    public function testSendVerificationEmail()
    {
        $user = factory(User::class)->create(['password' => bcrypt('123'), 'is_confirm' => false]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/Confirm')
                ->type('email', $user->email)
                ->press('Wyślij e-mail z linkiem aktywacyjnym')
                ->assertSee('Na podany adres e-mail został wysłany link aktywacyjny.');
        });
    }

    public function testSendVerificationFailedToDueInvalidEmail()
    {
        $faker = Factory::create();

        $this->browse(function (Browser $browser) use ($faker) {
            $browser->visit('/Confirm')
                ->type('email', $faker->email)
                ->press('Wyślij e-mail z linkiem aktywacyjnym')
                ->assertSee('Podany adres e-mail nie istnieje.');
        });
    }

    public function testSendVerificationFailedToDuePreviousVerificationProcess()
    {
        $user = factory(User::class)->create(['password' => bcrypt('123')]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/Confirm')
                ->type('email', $user->email)
                ->press('Wyślij e-mail z linkiem aktywacyjnym')
                ->assertSee('Ten adres e-mail jest już zweryfikowany.');
        });
    }

    public function testSendVerificationEmailWithDifferentEmail()
    {
        $user = factory(User::class)->create(['password' => bcrypt('123'), 'is_confirm' => false]);
        $faker = Factory::create();

        $this->browse(function (Browser $browser) use ($user, $faker) {
            $browser
                ->loginAs($user)
                ->visit('/Confirm')
                ->assertInputValue('email', $user->email)
                ->type('email', $email = $faker->email)
                ->press('Wyślij e-mail z linkiem aktywacyjnym')
                ->assertSee('Na podany adres e-mail został wysłany link aktywacyjny.');

            $user->refresh();

            $this->assertEquals($email, $user->email);
        });
    }
}
