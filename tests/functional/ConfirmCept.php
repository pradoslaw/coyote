<?php
use Coyote\Actkey;

$I = new FunctionalTester($scenario);
$I->wantTo('confirm my email address');

$userId = 100001;
$actkey = 'randomstring';

$I->haveRecord('users', [
    'id'         => $userId,
    'name'       => 'Joe Doe',
    'email'      => 'joe@doe.com',
    'password'   => bcrypt('123'),
    'created_at' => new DateTime(),
    'updated_at' => new DateTime(),
    'is_confirm' => 0
]);

// uzywamy modelu poniewaz w tabeli nie ma klucza "id"
Actkey::create([
    'actkey'     => $actkey,
    'user_id'    => $userId
]);

$I->amOnPage("/Confirm/Email?id=$userId&actkey=$actkey");
$I->see('Adres e-mail został pozytywnie potwierdzony');
$I->dontSeeRecord('actkeys', ['actkey' => $actkey, 'user_id' => $userId]);