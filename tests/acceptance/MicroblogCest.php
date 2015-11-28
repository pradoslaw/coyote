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

    // tests
    public function write(AcceptanceTester $I)
    {
        $I->wantTo('write a message');

        $I->amOnPage('/Mikroblogi');
        $I->submitForm('.microblog-submit', ['text' => 'Testowy wpis na mikroblogu']);
        $I->wait(1);
        $I->canSee('Testowy wpis na mikroblogu');
    }

    public function comment(AcceptanceTester $I)
    {
        $I->wantTo('post a comment');

        $I->amOnPage('/Mikroblogi');
        $I->canSeeElement('.comment-submit');
//        $I->submitForm('.comment-submit', ['text' => 'To jest unikalny komentarz']);
//        $I->wait(1);
//        $I->canSee('To jest unikalny komentarz');
    }
}
