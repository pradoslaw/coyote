<?php
use Coyote\Actkey;

class ConfirmCest
{
    public function confirmEmailAddressBeingLoggedOut(FunctionalTester $I)
    {
        $user = $I->createUser();
        $actkey = str_random();

        // uzywamy modelu poniewaz w tabeli nie ma klucza "id"
        Actkey::create([
            'actkey'     => $actkey,
            'user_id'    => $user->id
        ]);

        $I->amOnPage("/Confirm/Email?id={$user->id}&actkey=$actkey");
        $I->see('Adres e-mail został pozytywnie potwierdzony');
        $I->dontSeeRecord('actkeys', ['actkey' => $actkey, 'user_id' => $user->id]);
    }

    public function generateEmailLinkBeingLoggedIn(FunctionalTester $I)
    {
        $user = $I->createUser();
        $I->amLoggedAs($user);

        $I->amOnPage('Confirm');
        $I->fillField('email', $user->email);
        $I->click('button[type=submit]');
        $I->see('Na podany adres e-mail został wysłany link aktywacyjny.');

        $I->seeRecord('actkeys', ['user_id' => $user->id]);
        $row = $I->grabRecord('actkeys', ['user_id' => $user->id]);

        $I->amOnPage("/Confirm/Email?id=" . $user->id . "&actkey=" . $row['actkey']);
        $I->see('Adres e-mail został pozytywnie potwierdzony');
        $I->dontSeeRecord('actkeys', ['actkey' => $row['actkey'], 'user_id' => $user->id]);
    }

    public function tryToGenerateEmailButEmailDoesNotExist(FunctionalTester $I)
    {
        $I->amOnPage('Confirm');
        $I->fillField('email', 'somefakeemail@fakeemail.com');
        $I->click('button[type=submit]');

        $I->seeFormErrorMessage('email', 'Podany adres e-mail nie istnieje.');
    }

    public function tryToGenerateEmailButUserAlreadyConfirmedEmail(FunctionalTester $I)
    {
        $user = $I->createUser(['is_confirm' => 1]);

        $I->amOnPage('Confirm');
        $I->fillField('email', $user->email);
        $I->click('button[type=submit]');

        $I->seeFormErrorMessage('email', 'Ten adres e-mail jest już zweryfikowany.');
    }
}


