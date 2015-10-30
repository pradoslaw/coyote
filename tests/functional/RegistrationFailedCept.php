<?php
$I = new FunctionalTester($scenario);
$I->wantTo('attempt to register a user');

$I->amOnPage('/Register');
$I->fillField('name', 'Jan Kowalski');
$I->fillField('email', 'joe@doe.com');
$I->fillField('password', '123');
$I->fillField('password_confirmation', '123');
$I->click('button[type=submit]');

$I->amOnPage('/');
$I->dontSeeAuthentication();
