<?php

class TopicCest
{
    private $forum;

    public function _before(FunctionalTester $I)
    {
        $this->forum = $I->createForum();
    }

    public function testCreateLockedTopicAndTryToWriteNewPost(FunctionalTester $I)
    {
        $topic = $this->createLockedTopic($I);
        $I->dontSee('Odpowiedz', 'a');

        $I->amOnRoute('forum.topic.submit', [$this->forum->slug, $topic->id]);
        $I->seeResponseCodeIs(401);
    }

    public function testCreateLockedTopicAndTryToWriteNewPostAsAdmin(FunctionalTester $I)
    {
        $I->logInAsAdmin();
        $topic = $this->createLockedTopic($I);
        $I->see('Odpowiedz', 'a');

        $I->amOnRoute('forum.topic.submit', [$this->forum->slug, $topic->id]);
        $I->seeResponseCodeIs(200);
    }

    private function createLockedTopic(FunctionalTester $I)
    {
        $topic = $I->createTopic(['forum_id' => $this->forum->id, 'is_locked' => 1]);
        $I->createPost(['forum_id' => $this->forum->id, 'topic_id' => $topic->id]);

        $I->seeRecord('topics', ['id' => $topic->id, 'is_locked' => 1]);

        $I->amOnRoute('forum.category', [$this->forum->slug]);
        $I->see($topic->subject, 'a');
        $I->click($topic->subject, 'a');

        $I->see($topic->subject, 'h1');

        return $topic;
    }
}
