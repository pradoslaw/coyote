<?php

class PasswordCest
{
    private $user;

    public function _before(FunctionalTester $I)
    {
        $this->user = $I->createUser([
            'name'       => 'Joe Doe',
            'email'      => 'joe@doe.com',
            'password'   => '123',
            'is_confirm' => 0
        ]);
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function tryToRemindPasswordButUserDoesNotExist(FunctionalTester $I)
    {
        $I->amOnPage('/Password');
        $I->fillField('email', 'JOE@doe.com');
        $I->click('button[type=submit]');
        $I->see('Podany adres e-mail nie istnieje.');
    }

    public function tryToRemindPasswordButEmailIsNotConfirmed(FunctionalTester $I)
    {
        $I->amOnPage('/Password');
        $I->fillField('email', 'joe@doe.com');
        $I->click('button[type=submit]');
        $I->see('Ten adres e-mail nie został zweryfikowany.');
    }

    public function tryToRemindPasswordWithCaseInsensitiveEmail(FunctionalTester $I)
    {
        $this->user->is_confirm = true;
        $this->user->save();

        $I->amOnPage('/Password');
        $I->fillField('email', 'JOE@doe.com');
        $I->click('button[type=submit]');
        $I->see('Podany adres e-mail nie istnieje.');
    }

    public function successfullyRemindPassword(FunctionalTester $I)
    {
        $this->user->is_confirm = true;
        $this->user->save();

        $I->amOnPage('/Password');
        $I->fillField('email', 'joe@doe.com');
        $I->click('button[type=submit]');
        $I->see('Na podany adres e-mail wysłane zostały dalsze instrukcje');

        $I->seeRecord('users', ['name' => 'Joe Doe']);

        $I->seeRecord('password_resets', ['email' => 'joe@doe.com']);
        $record = $I->grabRecord('password_resets', ['email' => 'joe@doe.com']);

        $I->amOnPage('Password/reset/' . $record['token']);
        $I->fillField('email', 'joe@doe.com');
        $I->fillField('password', '1234');
        $I->fillField('password_confirmation', '1234');
        $I->click('button[type=submit]');
        $I->see('Hasło zostało ustawione. Zostałeś prawidłowo zalogowany.');
    }

    public function tryToRemindPasswordOfNotActiveUser(FunctionalTester $I)
    {
        $user = $I->createUser(['is_confirm' => 1, 'is_active' => 0]);

        $I->amOnPage('/Password');
        $I->fillField('email', $user->email);
        $I->click('button[type=submit]');
        $I->see('Ten użytkownik nie istnieje lub został usunięty.');
    }
}







