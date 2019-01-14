<?php


class LoginCest
{
    public function testLoginAsUser(FunctionalTester $I)
    {
        $I->wantTo('login as a user');

        factory(\Coyote\User::class)->create(['name' => 'Joe Doe', 'password' => bcrypt('123')]);

        $I->amOnPage('/Login');
        $I->fillField('name', 'Joe doe'); // case insensitive test
        $I->fillField('password', '123');
        $I->click('button[type=submit]');

        $I->amOnPage('/');
        $I->seeAuthentication();
        $I->see('Joe Doe', '.dropdown-username');
    }
}
