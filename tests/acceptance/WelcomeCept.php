<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('czy dziala strona glowna?');
$I->amOnPage('/');
$I->see('Co nowego na forum?');
?>
