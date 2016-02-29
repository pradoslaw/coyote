<?php
use Coyote\User;

$user = User::whereNull('provider_id')->first();

$I = new FunctionalTester($scenario);
$I->wantTo('Be logged as ' . $user->name);

$I->amLoggedAs($user);
$I->amOnPage('/');
$I->seeAuthentication();
$I->see($user->name, '.dropdown-userhome');