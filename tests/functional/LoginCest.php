<?php


class LoginCest
{
    public function testLoginAsUserUsingName(FunctionalTester $I)
    {
        $I->wantTo('login as a user');

        factory(\Coyote\User::class)->create(['name' => 'Joe Doe', 'password' => bcrypt('123')]);

        $I->amOnPage('/Login');
        $I->fillField('name', 'Joe doe'); // case insensitive test
        $I->fillField('password', '123');
        $I->click('button[type=submit]');

        $I->amOnPage('/');
        $I->seeAuthentication();
        $I->see('Joe Doe', '.profile-name');
    }

    public function testLoginAsUserUsingEmail(FunctionalTester $I)
    {
        $I->wantTo('login as a user using email');

        $user = factory(\Coyote\User::class)->create(['password' => bcrypt('123')]);

        $I->amOnPage('/Login');
        $I->fillField('name',  $user->email); // case insensitive test
        $I->fillField('password', '123');
        $I->click('button[type=submit]');

        $I->amOnPage('/');
        $I->seeAuthentication();
        $I->see($user->name, '.profile-name');
    }

    public function testLoginAsUserUsingNotVerifiedEmail(FunctionalTester $I)
    {
        $I->wantTo('login as a user using not verified email');

        $user = factory(\Coyote\User::class)->create(['password' => bcrypt('123'), 'is_confirm' => false]);

        $I->amOnPage('/Login');
        $I->fillField('name',  $user->email); // case insensitive test
        $I->fillField('password', '123');
        $I->click('button[type=submit]');

        $I->seeFormHasErrors();
    }
}
