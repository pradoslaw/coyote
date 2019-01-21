<?php

use Faker\Factory;

class TopicAcceptanceCest
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
        $I->see('Newbie');
        $I->click('Newbie', '.col-forum-description');
        $I->click('Nowy wątek');
        $I->fillField('user_name', ''); // specjalnie ustawiamy pusta wartosc (inaczej JS sam

        $I->click('Zapisz');

        $I->waitForText('Temat musi posiadać minimum 3 znaki długości.');
        $I->see('Proszę wpisać treść.');
        $I->see('Proszę wpisać nazwę użytkownika.');
    }

    public function createTopic(AcceptanceTester $I)
    {
        $I->wantTo('Create new topic');
        $I->amOnPage('/Forum/Newbie/Submit');

        $fake = Factory::create();
        $text = $fake->text;
        $subject = $fake->text(50);

        $this->submit($I, $subject, $text, $fake->userName);

        $I->wait(1);
        $I->canSee($text);
        $I->seeInTitle($subject);

        $I->amOnPage('/Forum/Newbie');
        $I->canSee($subject);
        $I->click($subject);
    }

    public function createTopicAndGetError(AcceptanceTester $I)
    {
        $I->wantTo('Try to create topic and get error message');
        $I->amOnPage('/Forum/Newbie/Submit');

        $fake = Factory::create();
        $this->submit($I, $fake->title, $fake->text, $fake->userName);

        $I->wait(1);
        $I->see('przed dodaniem kolejnego wpisu.');
    }

    public function createTopicAsAuthenticatedUser(AcceptanceTester $I)
    {
        $I->wantTo('Create new topic as authenticated user');
        $I->login('admin', '123');
        $I->amOnPage('/Forum/Newbie/Submit');

        $I->wait(15); // czekamy 15 sekund aby nie zalapac sie na walidacje spamowania

        $fake = Factory::create();
        $text = $fake->text;
        $subject = $fake->text(50);

        $this->submit($I, $subject, $text);

        $I->wait(1);
        $I->canSee($text);
        $I->seeInTitle($subject);

        $I->click('Szybka edycja');
        $I->wait(1);

        $I->seeInField('textarea[name=text]', $text);
        $I->fillField('.post-content textarea', 'edit');
        $I->click('Zapisz');

        $I->waitForText('edit', 10, '.post-content');
    }

    private function submit(AcceptanceTester $I, $subject, $text, $username = null)
    {
        if ($username) {
            $I->fillField('user_name', $username);
        }

        $I->fillField('subject', $subject);
        $I->fillField('text', $text);
        $I->click('Zapisz');
    }
}
