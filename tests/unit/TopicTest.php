<?php

use Coyote\Topic;
use Coyote\Post;
use Coyote\Forum;
use Coyote\User;
use Faker\Factory;

class TopicTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    protected $forum;
    protected $user;

    protected function _before()
    {
        $fake = Factory::create();
        $this->forum = Forum::create(['name' => $fake->name, 'path' => $fake->name, 'description' => 'Lorem ipsum']);
        $this->user = User::create(['name' => $fake->name, 'email' => $fake->email, 'password' => $fake->password]);
    }

    protected function _after()
    {
    }

    private function create($userId = null)
    {
        $userName = null;
        $fake = Factory::create();

        if (!$userId) {
            $userName = $fake->name;
        }

        $topic = Topic::create(['subject' => $fake->name, 'path' => $fake->name, 'forum_id' => $this->forum->id]);
        Post::create([
            'topic_id' => $topic->id,
            'forum_id' => $this->forum->id,
            'user_id' => $userId,
            'user_name' => $userName,
            'text' => $fake->text,
            'ip' => $fake->ipv4,
            'browser' => 'Chrome',
            'host' => 'localhost'
        ]);

        return $topic;
    }

    private function userHasPost($expected)
    {
        $user = $this->tester->grabRecord('users', ['id' => $this->user->id]);
        $this->assertEquals($expected, $user->posts);
    }

    // tests
    public function testTopicCreationAsAnonymousUser()
    {
        $this->tester->seeRecord('forums', ['name' => $this->forum->name]);
        $before = $this->tester->grabRecord('forums', ['id' => $this->forum->id]);

        $this->assertEquals(0, $before->topics);

        $this->create();
        $after = $this->tester->grabRecord('forums', ['id' => $this->forum->id]);

        $this->assertEquals(1, $after->topics);
        $this->assertEquals(0, $this->tester->grabRecord('topics', ['forum_id' => $this->forum->id])->replies);
    }

    public function testTopicCreationAsRegisteredUser()
    {
        $this->create($this->user->id);
        $this->userHasPost(1);
    }

    public function testTopicDelete()
    {
        $topic = $this->create();
        $topic->forceDelete();

        $forum = $this->tester->grabRecord('forums', ['id' => $this->forum->id]);
        $this->assertEquals(0, $forum->topics);
        $this->assertEquals(null, $forum->last_post_id);
        $this->tester->dontSeeRecord('posts', ['forum_id' => $this->forum->id]);
    }

    public function testTopicSoftDelete()
    {
        $topic = $this->create($this->user->id);
        $this->userHasPost(1);
        $topic->delete();

        $this->tester->seeRecord('topics', ['forum_id' => $topic->forum_id]);
        $topic = $this->tester->grabRecord('topics', ['forum_id' => $topic->forum_id]);

        $this->assertEquals(0, $topic->replies);
        $this->assertEquals(0, $topic->replies_real);

        $forum = $this->tester->grabRecord('forums', ['id' => $this->forum->id]);

        $this->assertEquals(0, $forum->topics);
        $this->assertEquals(0, $forum->posts);
        $this->assertEquals(null, $forum->last_post_id);
        $this->tester->seeRecord('posts', ['forum_id' => $this->forum->id]);

        $this->userHasPost(0);
    }

    public function testTopicRestore()
    {
        $topic = $this->create();

        $before = $this->tester->grabRecord('forums', ['id' => $this->forum->id]);
        $topic->delete();
        $topic->restore();
        $after = $this->tester->grabRecord('forums', ['id' => $this->forum->id]);

        $this->assertEquals($before->topics, $after->topics);
        $this->assertEquals(1, $after->posts);

        $this->tester->seeRecord('posts', ['forum_id' => $this->forum->id, 'deleted_at' => null]);

        $forum = $this->tester->grabRecord('forums', ['id' => $this->forum->id]);

        $this->assertEquals(1, $forum->topics);
        $this->assertEquals(1, $forum->posts);
        $this->assertNotNull($forum->last_post_id);
    }

    public function testTopicMove()
    {
        $fake = Factory::create();
        $topic = $this->create();

        $this->assertEquals(1, $this->tester->grabRecord('forums', ['id' => $this->forum->id])->topics);

        $newForum = Forum::create(['name' => $fake->name, 'path' => $fake->name, 'description' => 'Lorem ipsum']);

        $topic->forum_id = $newForum->id;
        $topic->save();

        $this->assertEquals(1, $this->tester->grabRecord('forums', ['id' => $newForum->id])->topics);
        $this->tester->dontSeeRecord('posts', ['forum_id' => $this->forum->id]);
    }
}