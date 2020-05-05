<?php

namespace Tests\Browser;

use Coyote\User;
use Faker\Factory;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class MicroblogTest extends DuskTestCase
{
    public function testSubmitValid()
    {
        $user = factory(User::class)->create();

        $this->browse(function (Browser $browser) use ($user) {
            $faker = Factory::create();

            $browser
                ->loginAs($user)
                ->visit('/Mikroblogi')
                ->type('text', $text = $faker->text())
                ->press('Zapisz')
                ->waitForText($text)
                ->assertSee($text);

            $browser->logout();
        });
    }

    public function testSubmitInvalid()
    {
        $user = factory(User::class)->create();

        $this->browse(function (Browser $browser) use ($user) {
            $faker = Factory::create();

            $browser
                ->loginAs($user)
                ->visit('/Mikroblogi')
                ->press('Zapisz')
                ->waitForText('Proszę wpisać treść')
                ->assertSee('Proszę wpisać treść');

            $browser->logout();
        });
    }
}
