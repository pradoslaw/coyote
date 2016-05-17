<?php
$I = new FunctionalTester($scenario);
$I->wantTo('pretend to be bot and try to add new pastebin entry');

$I->amOnRoute('pastebin.show');
$I->fillField('text', 'Lorem ipsum');
$I->fillField('title', 'no name');
$I->fillField('human_email', 'some@email.com');
$I->click('Zapisz');

$I->seeFormHasErrors();

$I->fillField('human_email', '');
$I->click('Zapisz');

$I->dontSeeFormErrors();
