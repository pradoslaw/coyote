<?php

class ForumCest
{
    public function visitForumForTheFirstTime(FunctionalTester $I)
    {
        $forum = $I->createForum();
        $topic = $I->createTopic(['forum_id' => $forum->id]);

        $I->createPost(['forum_id' => $forum->id, 'topic_id' => $topic->id]);

        sleep(1);
        $I->amOnRoute('forum.home');

        $sessionId = $I->getApplication()['request']->session()->getId();
        $I->seeRecord('sessions', ['id' => $sessionId]);
        $I->dontSeeRecord('session_log', ['id' => $sessionId]);

        $I->seeElement('//tr[@id=\'forum-' . $forum->id . '\']/td/span[@title=\'Brak nowych postów\']');

        $I->createPost(['forum_id' => $forum->id, 'topic_id' => $topic->id]);

        $I->amOnRoute('forum.home');
        $I->seeElement('//tr[@id=\'forum-' . $forum->id . '\']/td/a[@title=\'Kliknij, aby oznaczyć jako przeczytane\']');

        $I->amOnRoute('forum.topic', [$forum->slug, $topic->id, $topic->slug]);
        $I->see($topic->subject);

//        $I->amOnRoute('forum.home');
//
//        $I->seeElement('//tr[@id=\'forum-' . $forum->id . '\']/td/span[@title=\'Brak nowych postów\']');
    }

    public function testCreateForum(FunctionalTester $I)
    {
        $I->logInAsAdmin();
        $forum = $I->createForum();

        $I->amOnRoute('forum.home');
        $I->see($forum->name, 'a');
        $I->click($forum->name, 'a');
        $I->seeResponseCodeIs(200);
    }

    public function testTryToCreateNewTopicInLockedForumAndSeeErrorMessage(FunctionalTester $I)
    {
        $forum = $I->createForum(['is_locked' => 1]);
        $topic = $I->createTopic(['forum_id' => $forum->id]);
        $I->createPost(['forum_id' => $forum->id, 'topic_id' => $topic->id]);

        $I->amOnRoute('forum.home');
        $I->see($forum->name, 'a');
        $I->click($forum->name, 'a');
        $I->dontSee('Nowy wątek');

        $I->see($topic->subject);
        $I->click($topic->subject);

        $I->dontSee('Nowy wątek');

        $I->seeInTitle($topic->subject);
        $I->dontSee('Nowy wątek');

        $I->amOnRoute('forum.topic.submit', [$forum->slug]);
        $I->seeResponseCodeIs(401);
    }

    public function testTryToCreateNewTopicInLockedForumAndSeeErrorMessageAsAdmin(FunctionalTester $I)
    {
        $I->logInAsAdmin();

        $this->testTryToCreateNewTopicInLockedForumAndSeeErrorMessage($I);
    }

    public function testTryToCreateNewTopicAsAnonymousInCategoryOnlyForRegisteredUsersAndSeeerrorMessage(FunctionalTester $I)
    {
        $forum = $I->createForum(['enable_anonymous' => 0]);
        $I->logout();

        $I->cantSeeAuthentication();

        $I->amOnRoute('forum.home');
        $I->see($forum->name, 'a');
        $I->click($forum->name, 'a');
        $I->see('Nowy wątek');
        $I->click('Nowy wątek');

        $I->seeInTitle('Logowanie');
    }
}
