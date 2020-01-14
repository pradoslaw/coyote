<?php

namespace Tests\Browser;

use Carbon\Carbon;
use Coyote\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Faker\Factory;

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
                    ->press('Utwórz konto')
                    ->assertPathIs('/User');
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
                ->press('Utwórz konto')
                ->assertSee('Konto o podanej nazwie użytkownika już istnieje.');
        });
    }
}
