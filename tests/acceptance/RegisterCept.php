<?php
use Faker\Factory;



$fake = Factory::create();
$name = $fake->userName;

$I = new AcceptanceTester($scenario);
$I->wantTo('register a user');
//$I->getModule('WebDriver')->webDriver->manage()->deleteAllCookies();

$I->amOnPage('/Register');

$I->wait(1);
$I->fillField('name', $name);
$I->fillField('email', $fake->email);
$I->fillField('password', 'password');
$I->fillField('password_confirmation', 'password');
$I->canSeeCheckboxIsChecked('input[name=human]');
$I->click('button[type=submit]');

$I->seeInCurrentUrl('/User');
$I->click('.dropdown-toggle', '.avatar');
$I->see($name, '.dropdown-username');
