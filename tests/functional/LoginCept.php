<?php
$I = new FunctionalTester($scenario);
$I->wantTo('login as a user');

$I->haveRecord('users', [
    'name' => 'Joe Doe',
    'email' =>  'joe@doe.com',
    'password' => bcrypt('123'),
    'created_at' => new DateTime(),
    'updated_at' => new DateTime(),
]);

$I->amOnPage('/Login');
$I->fillField('name', 'Joe Doe');
$I->fillField('password', '123');
$I->click('button[type=submit]');

$I->amOnPage('/');
$I->seeAuthentication();
$I->see('Joe Doe', '.dropdown-username');