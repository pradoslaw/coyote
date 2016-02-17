<?php
use Coyote\User;

$user = User::first();

$I = new FunctionalTester($scenario);
$I->wantTo('Be logged as ' . $user->name);

$I->amLoggedAs($user);
$I->amOnPage('/');
$I->see($user->name, '.dropdown-username');