<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Faker\Factory;

class RegisterTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->browse(function (Browser $browser) {
            $faker = Factory::create();

            $browser->visit('/Register')
                    ->type('name', $faker->name)
                    ->type('email', $faker->email)
                    ->type('password', $password = $faker->password)
                    ->type('password_confirmation', $password)
                    ->press('Utwórz konto')
                    ->assertPathIs('/User');
        });
    }
}
