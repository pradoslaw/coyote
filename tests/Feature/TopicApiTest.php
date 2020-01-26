<?php

namespace Tests\Feature;

use Coyote\Forum;
use Coyote\Guest;
use Coyote\Post;
use Coyote\Services\Forum\Tracker;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TopicApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var Forum
     */
    private $forum;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Topic
     */
    private $topic;

    /**
     * @var string
     */
    private $token;

    public function setUp()
    {
        parent::setUp();

        $this->user = $this->createUserWithGroup();

        $this->forum = $this->createForum([], $this->user->groups()->first()->id);
        $this->topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);

        $this->token = $this->user->createToken('4programmers.net')->accessToken;
    }

    public function testShowAllTopics()
    {
        $request = $this->get('/v1/topics', ['Accept' => 'application/json']);

        $data = $request->decodeResponseJson('data');

        $this->assertNotEquals($this->topic->subject, $data[0]['subject']);
        $this->assertTrue($data[0]['is_read']);
    }

    public function testShowAllTopicsAuthorized()
    {
        $request = $this->get('/v1/topics', ['Accept' => 'application/json', 'Authorization' => 'Bearer ' . $this->token]);
        $data = $request->decodeResponseJson('data');

        $this->assertEquals($this->topic->subject, $data[0]['subject']);
        $this->assertTrue($data[0]['is_read']);
    }

    public function testShowForbiddenWhenUnauthorized()
    {
        $request = $this->get('/v1/topics/' . $this->topic->id, ['Accept' => 'application/json']);

        $request->assertForbidden();
    }

    public function testShowTopicWhenAuthorized()
    {
        $request = $this->get('/v1/topics/' . $this->topic->id, ['Accept' => 'application/json', 'Authorization' => 'Bearer ' . $this->token]);

        $request->assertJsonFragment([
            'subject' => $this->topic->subject,
            'forum' => [
                'id' => $this->forum->id,
                'name' => $this->forum->name,
                'slug' => $this->forum->slug
            ]
        ]);
    }

    public function testMarkAsRead()
    {
        Tracker::make($this->topic, $this->user->guest_id)->asRead($this->topic->last_post_created_at);

        $request = $this->get('/v1/topics/' . $this->topic->id, ['Accept' => 'application/json', 'Authorization' => 'Bearer ' . $this->token]);
        $data = $request->decodeResponseJson();

        $this->assertTrue($data['is_read']);
    }

    public function testShowAllTopicsWithMarkedAsRead()
    {
        Tracker::make($this->topic, $this->user->guest_id)->asRead($this->topic->last_post_created_at);

        $request = $this->get('/v1/topics', ['Accept' => 'application/json', 'Authorization' => 'Bearer ' . $this->token]);
        $data = $request->decodeResponseJson('data');

        $this->assertEquals($this->topic->subject, $data[0]['subject']);
        $this->assertTrue($data[0]['is_read']);
    }

    public function testShowTopicAsNew()
    {
        Guest::forceCreate(['id' => $this->user->guest_id, 'updated_at' => now()->subMinute(5)]);

        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);
        factory(Post::class)->create(['topic_id' => $topic->id, 'forum_id' => $this->forum->id, 'created_at' => now()]);

        $request = $this->get('/v1/topics/' . $this->topic->id, ['Accept' => 'application/json', 'Authorization' => 'Bearer ' . $this->token]);
        $data = $request->decodeResponseJson();

        $this->assertFalse($data['is_read']);
    }
}
