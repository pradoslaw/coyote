<?php

namespace Tests\Browser;

use Carbon\Carbon;
use Coyote\User;
use Faker\Factory;
use Laravel\Dusk\Browser;

class RegisterTest extends DuskTestCase
{
    public function testRegisterUser()
    {
        $this->browse(function (Browser $browser) {
            $faker = Factory::create();

            $browser->visit('/Register')
                    ->type('name', $faker->userName)
                    ->type('email', $faker->email)
                    ->type('password', $password = $faker->password)
                    ->type('password_confirmation', $password)
                    ->check('label[for="terms"]')
                    ->press('Utwórz konto')
                    ->assertPathIs('/User');

            $browser->logout();
        });
    }

    public function testRegisterUserPreviouslyDeleted()
    {
        $user = factory(User::class)->create(['deleted_at' => Carbon::now()]);

        $this->browse(function (Browser $browser) use ($user) {
            $faker = Factory::create();

            $browser->visit('/Register')
                ->type('name', $user->name)
                ->type('email', $faker->email)
                ->type('password', $password = $faker->password)
                ->type('password_confirmation', $password)
                ->check('label[for="terms"]')
                ->press('Utwórz konto')
                ->assertSee('Konto o podanej nazwie użytkownika już istnieje.');
        });
    }
}
