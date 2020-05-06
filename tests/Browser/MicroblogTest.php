<?php

namespace Tests\Browser;

use Coyote\Microblog;
use Coyote\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
            $browser
                ->loginAs($user)
                ->visit('/Mikroblogi')
                ->press('Zapisz')
                ->waitForText('Proszę wpisać treść')
                ->assertSee('Proszę wpisać treść');

            $browser->logout();
        });
    }

    public function testSponsoredOnHomepage()
    {
        $microblog = factory(Microblog::class)->create(['is_sponsored' => true]);

        try {
            $this->browse(function (Browser $browser) use ($microblog) {
                $browser
                    ->visit('/')
                    ->waitForText($microblog->text)
                    ->assertSee($microblog->text);
            });
        } finally {
            $microblog->forceDelete();
        }
    }
}
