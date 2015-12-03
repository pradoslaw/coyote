<?php

use Coyote\User;
use Faker\Factory;

class MicroblogsCest
{
    private $user;

    public function _before(FunctionalTester $I)
    {
        $this->user = User::first();
        $I->amLoggedAs($this->user);
    }

    public function _after(FunctionalTester $I)
    {
    }

    private function grabUser(FunctionalTester $I)
    {
        return $I->grabRecord('users', ['id' => $this->user->id]);
    }

    // tests
    public function createMicroblogAndReceiveReputationPoints(FunctionalTester $I)
    {
        $before = $this->grabUser($I);

        $fake = Factory::create();
        $text = $fake->realText();

        $I->disableMiddleware();
        $I->amOnRoute('microblog.home');
        $I->submitForm('.microblog-submit', ['text' => $text]);

        $I->seeRecord('microblogs', ['text' => $text]);

        $after = $this->grabUser($I);
        $I->assertGreaterThan($before->reputation, $after->reputation);
    }

    public function createMicroblogWithUserMentions(FunctionalTester $I)
    {
        $fake = Factory::create();

        $login = $fake->firstName;
        $text = 'How are you @' . $login;

        $userId = $I->haveRecord('users', [
            'name' => $login,
            'password' => $fake->password(),
            'email' => $fake->email,
            'created_at' => new DateTime(),
            'updated_at' => new DateTime()
        ]);

        $I->disableMiddleware();
        $I->amOnRoute('microblog.home');
        $I->seeElement('.microblog-submit');
        $I->submitForm('.microblog-submit', ['text' => $text]);

        $I->amOnRoute('microblog.home');
        $I->see($login);

        $I->seeRecord('microblogs', ['text' => $text]);

        $alert = $I->grabRecord('alerts', ['user_id' => $userId]);

        // tytul powiadomienia
        $I->assertEquals($text, $alert->subject);

        $sender = $I->grabRecord('alert_senders', ['alert_id' => $alert->id, 'user_id' => $this->user->id]);
        $I->assertEquals($sender->name, $this->user->name);
    }

    public function tryingToCreateWithEmptyContent(FunctionalTester $I)
    {
        $I->disableMiddleware();
        $I->amOnRoute('microblog.home');
        $I->sendAjaxPostRequest(route('microblog.save'), array('text' => '')); // POST

        $I->seeResponseCodeIs(422);
    }

    public function canEditMicroblog(FunctionalTester $I)
    {
        $fake = Factory::create();
        $text = $fake->text();

        $id = $I->haveRecord('microblogs', [
            'user_id' => $this->user->id,
            'text' => $text,
            'created_at' => new DateTime(),
            'updated_at' => new DateTime(),
            'score' => 0
        ]);

        $I->amOnRoute('microblog.save', [$id]);
        $I->see($text);
    }
}
