<?php

class ForumCest
{
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
