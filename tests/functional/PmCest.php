<?php

use Coyote\User;
use Faker\Factory;

class PmCest
{
    private $user;
    private $recipient;

    public function _before(FunctionalTester $I)
    {
        $this->user = $I->logInAsRandomUser();
        $this->recipient = $I->createUser();
    }

    public function _after(FunctionalTester $I)
    {
    }

    // tests
    public function writePrivateMessage(FunctionalTester $I)
    {
        $fake = Factory::create();
        $text = $fake->realText();

        $I->amOnRoute('user.pm.submit');
        $I->fillField(['name' => 'recipient'], $this->recipient['name']);
        $I->fillField(['name' => 'text'], $text);
        $I->click('Wyślij', '#box-pm');

        $I->see($text);
    }

    public function tryToWriteMessageWithoutRecipient(FunctionalTester $I)
    {
        $I->amOnRoute('user.pm.submit');
        $I->click('Wyślij', '#box-pm');
        $I->seeFormHasErrors();
        $I->seeFormErrorMessage('recipient', 'Proszę wpisać nadawcę wiadomości.');
        $I->seeFormErrorMessage('text', 'Proszę wpisać treść');
    }

    public function tryToWriteMessagetoMyselfAndFail(FunctionalTester $I)
    {
        $I->amOnRoute('user.pm.submit');
        $I->fillField(['name' => 'recipient'], $this->user['name']);
        $I->click('Wyślij', '#box-pm');
        $I->seeFormErrorMessage('recipient', 'Nie można wysłać wiadomości do samego siebie.');
    }
}
