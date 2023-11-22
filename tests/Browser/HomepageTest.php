<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;

class HomepageTest extends DuskTestCase
{
    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testBasicExample()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSee('Co nowego na forum?');
        });
    }
}
