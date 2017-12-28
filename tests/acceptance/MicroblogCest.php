<?php

class MicroblogCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->login('admin', '123');
    }

    public function _after(AcceptanceTester $I)
    {

    }

    public function postWithEmptyContent(AcceptanceTester $I)
    {
        $I->wantTo('Create message with empty content');

        $I->amOnPage('/Mikroblogi');
        $I->submitForm('.microblog-submit', ['text' => '']);
        $I->waitForText('Proszę wpisać treść');
    }

    // tests
    public function post(AcceptanceTester $I)
    {
        $I->wantTo('write a message');

        $I->amOnPage('/Mikroblogi');
        $I->submitForm('.microblog-submit', ['text' => 'Testowy wpis na mikroblogu']);
        $I->wait(1);
        $I->canSee('Testowy wpis na mikroblogu');
    }

    public function postAndGetError(AcceptanceTester $I)
    {
        $I->wantTo('write new message but it is too fast so I suppose to see error message');

        $I->amOnPage('/Mikroblogi');
        $I->submitForm('.microblog-submit', ['text' => 'Kolejny wpis']);
        $I->wait(1);
        $I->see('przed dodaniem kolejnego wpisu.'); // wystarczy tylko fragment tego zdania
    }

    public function comment(AcceptanceTester $I)
    {
        $I->wantTo('post a comment');

        $I->amOnPage('/Mikroblogi');
        $I->canSeeElement('.comment-form');
//        $I->submitForm('.comment-submit', ['text' => 'To jest unikalny komentarz']);
//        $I->wait(1);
//        $I->canSee('To jest unikalny komentarz');
    }
}
