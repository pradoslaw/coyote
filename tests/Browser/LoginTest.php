<?php

namespace Tests\Browser;

use Coyote\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    public function testSuccessfulLogin()
    {
        $user = factory(User::class)->create(['password' => bcrypt('123')]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/Login')
                ->type('name', $user->name)
                ->type('password', '123')
                ->press('Logowanie')
                ->assertPathIs('/')
                ->assertAuthenticated();
        });
    }

    public function testSuccessfulLoginUsingEmail()
    {
        $user = factory(User::class)->create(['password' => bcrypt('123')]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/Login')
                ->type('name', $user->email)
                ->type('password', '123')
                ->press('Logowanie')
                ->assertPathIs('/')
                ->assertAuthenticated();
        });
    }

    public function testFailedLoginDueToBlockedUser()
    {
        $user = factory(User::class)->create(['password' => bcrypt('123'), 'is_blocked' => true]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/Login')
                ->type('name', $user->email)
                ->type('password', '123')
                ->press('Logowanie')
                ->assertSee('Konto o tym loginie zostało zablokowane.');
        });
    }

    public function testFailedLoginDueToFailedLogin()
    {
        $user = factory(User::class)->create(['password' => bcrypt('123')]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/Login')
                ->type('name', $user->name . '345345')
                ->type('password', '123')
                ->press('Logowanie')
                ->assertSee('Użytkownik o podanej nazwie nie istnieje.');
        });
    }

    public function testFailedLoginDueToFailedPassword()
    {
        $user = factory(User::class)->create(['password' => bcrypt('123')]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/Login')
                ->type('name', $user->name)
                ->type('password', '1235')
                ->press('Logowanie')
                ->assertSee('Podane hasło nie jest prawidłowe.');
        });
    }
}
