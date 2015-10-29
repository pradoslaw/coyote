<?php
$I = new FunctionalTester($scenario);
$I->wantTo('test form error assertions');

$I->amOnPage('/');
$I->click('Rejestracja', '.btn-register');

$I->seeCurrentUrlEquals('/Register');
$I->click('button[type=submit]');
$I->seeFormHasErrors();
$I->seeFormErrorMessages(array(
    'name' => 'Pole nazwa użytkownika jest wymagane.',
    'email' => 'Pole email jest wymagane.',
    'password' => 'Pole hasło jest wymagane.'
));

$I->fillField('name', '^%&%%$^$');
$I->fillField('email', 'johndoe@example.com');
$I->fillField('password',  'password');
$I->fillField('password_confirmation',  'password');
$I->click('button[type=submit]');
$I->seeFormErrorMessage('name', 'Nazwa użytkownika może zawierać litery, cyfry oraz znaki ._ -');