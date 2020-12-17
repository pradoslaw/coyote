<?php

namespace Tests\Browser;

use Coyote\Microblog;
use Coyote\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class MicroblogTest extends DuskTestCase
{
    use WithFaker;

    public function testSubmitValid()
    {
        $user = factory(User::class)->create();

        $this->browse(function (Browser $browser) use ($user) {
            $faker = Factory::create();
            $text = $faker->text();

            $browser
                ->loginAs($user)
                ->visit('/Mikroblogi')
                ->type('text', $text)
                ->press('Zapisz')
                ->waitForText($text)
                ->assertSee($text)
                ->assertInputValueIsNot('text', $text);

            $browser->logout();
        });
    }

    public function testSubmitComment()
    {
        $microblog = factory(Microblog::class)->create();

        $this->browse(function (Browser $browser, Browser $browser2) use ($microblog) {
            $user = factory(User::class)->create();
            $text = $this->faker->text();

            $browser2->loginAs($user)->visit('/Mikroblogi/View/' . $microblog->id)->waitForText($microblog->text);

            $browser
                ->loginAs($user)
                ->visit('/Mikroblogi/View/' . $microblog->id)
                ->type('text', $text)
                ->click('.btn-comment-submit')
                ->waitForText($text)
                ->assertSee($text)
                ->assertInputValueIsNot('text', $text);

//            $browser2->waitForText($text, 10);

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
