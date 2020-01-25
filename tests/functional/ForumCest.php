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

    public function testTryToCreateNewPostWhileTopicWasMoved(FunctionalTester $I)
    {
        $forum = $I->createForum();
        $topic = $I->createTopic(['forum_id' => $forum->id]);

        $I->amOnRoute('forum.post.submit', [$forum, $topic]);
        $I->seeInCurrentUrl($forum->slug);
        $I->fillField('text', 'Lorem ipsum');

        $newForum = $I->createForum();
        $topic->forum_id = $newForum->id;
        $topic->save(); // move topic to new category

        $I->click('Zapisz');

        $I->canSeeInCurrentUrl($newForum->slug); // see new slug
    }

    public function testTryToCreateNewPostWhileTopicWasMovedToLockedCategory(FunctionalTester $I)
    {
        $forum = $I->createForum();
        $topic = $I->createTopic(['forum_id' => $forum->id]);

        $I->amOnRoute('forum.post.submit', [$forum, $topic]);
        $I->seeInCurrentUrl($forum->slug);
        $I->fillField('text', 'Lorem ipsum');

        $newForum = $I->createForum(['is_locked' => true]);
        $topic->forum_id = $newForum->id;
        $topic->save(); // move topic to new category

        $I->click('Zapisz');

        $I->canSeeResponseCodeIs(401);
    }

    public function testTryToCreateNewPostWhileCategoryIsNotAccessableAnymore(FunctionalTester $I)
    {
        $forum = $I->createForum();
        $topic = $I->createTopic(['forum_id' => $forum->id]);

        $I->amOnRoute('forum.post.submit', [$forum, $topic]);
        $I->seeInCurrentUrl($forum->slug);
        $I->fillField('text', 'Lorem ipsum');

        $adminUser = $I->createUser();
        $group = $I->haveRecord(\Coyote\Group::class, ['name' => 'Secret group']);

        $I->haveRecord(\Coyote\Group\User::class, ['group_id' => $group->id, 'user_id' => $adminUser->id]);
        $I->haveRecord(\Coyote\Forum\Access::class, ['forum_id' => $forum->id, 'group_id' => $group->id]);
        $forum->is_prohibited = true;
        $forum->save();

        $I->click('Zapisz');

        $I->canSeeResponseCodeIs(403);
    }

    public function testTryToCreateNewPostWhileTopicWasLocked(FunctionalTester $I)
    {
        $forum = $I->createForum();
        $topic = $I->createTopic(['forum_id' => $forum->id]);

        $I->amOnRoute('forum.post.submit', [$forum, $topic]);
        $I->seeInCurrentUrl($forum->slug);
        $I->fillField('text', 'Lorem ipsum');

        $topic->is_locked = true;
        $topic->save();

        $I->click('Zapisz');

        $I->canSeeResponseCodeIs(401);
    }
}
