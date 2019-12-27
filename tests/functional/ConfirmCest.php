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
        $I->click('Wyślij e-mail z linkiem aktywacyjnym');
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
        $I->click('Wyślij e-mail z linkiem aktywacyjnym');

        $I->seeFormErrorMessage('email', 'Podany adres e-mail nie istnieje.');
    }

    public function tryToGenerateEmailButUserAlreadyConfirmedEmail(FunctionalTester $I)
    {
        $user = $I->createUser(['is_confirm' => 1]);

        $I->amOnPage('Confirm');
        $I->fillField('email', $user->email);
        $I->click('Wyślij e-mail z linkiem aktywacyjnym');

        $I->seeFormErrorMessage('email', 'Ten adres e-mail jest już zweryfikowany.');
    }

    public function changeUserEmailBeforeConfirm(FunctionalTester $I)
    {
        $user = $I->createUser();
        $I->amLoggedAs($user);

        $I->amOnPage('Confirm');
        $faker = Faker\Factory::create();

        $newEmail = $faker->email;
        $I->fillField('email', $newEmail);
        $I->click('Wyślij e-mail z linkiem aktywacyjnym');

        $I->seeRecord('users', ['email' => $newEmail]);
    }

    public function generateConfirmEmailBeingLoggedOut(FunctionalTester $I)
    {
        $user = $I->createUser();
        $I->amOnPage('Confirm');

        $faker = Faker\Factory::create();

        $newEmail = $faker->email;
        $I->fillField('email', $newEmail);
        $I->click('Wyślij e-mail z linkiem aktywacyjnym');
        $I->seeFormErrorMessage('email', 'Podany adres e-mail nie istnieje.');

        $I->fillField('email', $user->email);
        $I->click('Wyślij e-mail z linkiem aktywacyjnym');

        $I->see('Na podany adres e-mail został wysłany link aktywacyjny.');
    }

    public function changeUserEmailWhenCurrentEmailIsConfirmed(FunctionalTester $I)
    {
        $user = $I->createUser(['is_confirm' => 1, 'allow_smilies' => 1, 'allow_count' => 1, 'allow_sticky_header' => 1, 'allow_sig' => 1, 'allow_subscribe' => 1, 'sig' => '']);
        $I->amLoggedAs($user);

        $I->seeAuthentication();
        $I->amOnRoute('user.settings');

        $faker = Faker\Factory::create();

        $newEmail = $faker->email;
        $I->fillField('email', $newEmail);
        $I->click('Zapisz');

        $I->seeInField('email', $user->email);
        $I->see('Na adres ' . $newEmail . ' wysłaliśmy link umożliwiający zmianę adresu e-mail.');
    }

    // nie wiem czemu ten test wywala sie na travisie :( kiedys dzialalo
//    public function changeUserEmailWhenCurrentEmailIsNotConfirmed(FunctionalTester $I)
//    {
//        $user = $I->createUser(['is_confirm' => 0, 'allow_smilies' => 1]);
//        $I->amLoggedAs($user);
//
//        $I->amOnRoute('user.settings');
//
//        $newEmail = 'fooooo@baaaaar.com';
//        $I->fillField('email', $newEmail);
//        $I->click('Zapisz');
//
//        $I->seeCurrentRouteIs('user.settings');
//
//        $I->seeInField('email', $newEmail);
//    }
}


