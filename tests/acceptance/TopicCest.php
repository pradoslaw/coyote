<?php

use Faker\Factory;

class ForumCest
{
    public function _before(AcceptanceTester $I)
    {
//        $I->login('admin', '123');
    }

    public function _after(AcceptanceTester $I)
    {

    }

    public function createTopicWithEmptyContent(AcceptanceTester $I)
    {
        $I->wantTo('Create post with empty content');

        $I->amOnPage('/Forum');
        $I->click('Newbie');
        $I->click('Nowy wątek');

        $I->click('Wyślij');

        $I->wait(1);
        $I->see('Temat musi posiadać minimum 3 znaki długości.');
        $I->see('Proszę wpisać treść.');
        $I->see('Proszę wpisać nazwę użytkownika.');
    }

    // tests
    public function createTopic(AcceptanceTester $I)
    {
        $I->wantTo('Create new topic');
        $I->amOnPage('/Forum/Newbie/Submit');

        $fake = Factory::create();
        $text = $fake->text;
        $subject = $fake->title;

        $this->submit($I, $subject, $text, $fake->userName);

        $I->wait(1);
        $I->canSee($text);
        $I->seeInTitle($subject);
    }

    public function createTopicAndGetError(AcceptanceTester $I)
    {
        $I->wantTo('Try to create topic and get error message');
        $I->amOnPage('/Forum/Newbie/Submit');

        $fake = Factory::create();
        $this->submit($I, $fake->title, $fake->text, $fake->userName);
        $I->see('Musisz odczekać chwilę przed dodaniem kolejnego wpisu.');
    }

    private function submit(AcceptanceTester $I, $subject, $text, $username)
    {
        $I->fillField('user_name', $username);
        $I->fillField('subject', $subject);
        $I->fillField('text', $text);
        $I->click('Wyślij');
    }
}
