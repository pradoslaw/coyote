<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('register a user');

$I->amOnPage('/Register');
$I->wait(1);
$I->fillField('name', 'Joe Doe');
$I->fillField('email', 'example@example.com');
$I->fillField('password', 'password');
$I->fillField('password_confirmation', 'password');
$I->click('button[type=submit]');

$I->amOnPage('/');
$I->see('Joe Doe', '.dropdown-username');