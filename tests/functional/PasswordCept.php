<?php

$I = new FunctionalTester($scenario);
$I->wantTo('remind my password');

$user = $I->haveRecord('users', [
    'name'       => 'Joe Doe',
    'email'      => 'joe@doe.com',
    'password'   => bcrypt('123'),
    'created_at' => new DateTime(),
    'updated_at' => new DateTime(),
    'is_confirm' => 1
]);

$I->amOnPage('/Password');
$I->fillField('email', 'joe@doe.com');
$I->click('button[type=submit]');
$I->see('Na podany adres e-mail wysłane zostały dalsze instrukcje');

$I->seeRecord('password_resets', ['email' => 'joe@doe.com']);
$record = $I->grabRecord('password_resets', ['email' => 'joe@doe.com']);

$I->amOnPage('Password/reset/' . $record->token);
$I->fillField('email', 'joe@doe.com');
$I->fillField('password', '1234');
$I->fillField('password_confirmation', '1234');
$I->click('button[type=submit]');
$I->see('Hasło zostało prawidłowo ustawione');