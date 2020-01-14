<?php

namespace Tests\Feature;

use Coyote\Forum;
use Coyote\Group;
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

    public function setUp()
    {
        parent::setUp();

        $this->user = $this->createUserWithGroup();

        $this->forum = factory(Forum::class)->create(['is_prohibited' => true]);
        $this->forum->access()->create(['group_id' => $this->user->group()->first()->id]);
        $this->topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);
    }

    public function testShowAllTopics()
    {
        $request = $this->get('/v1/topics', ['Accept' => 'application/json']);

        $data = $request->decodeResponseJson('data');

        $this->assertNotEquals($this->topic->subject, $data[0]['subject']);
        $this->assertNull($data[0]['read_at']);
        $this->assertTrue($data[0]['is_read']);
    }

    public function testShowAllTopicsAuthorized()
    {
        $this->actingAs($this->user, 'api');

        $request = $this->get('/v1/topics', ['Accept' => 'application/json']);
        $data = $request->decodeResponseJson('data');

        $this->assertEquals($this->topic->subject, $data[0]['subject']);
        $this->assertNull($data[0]['read_at']);
        $this->assertTrue($data[0]['is_read']);
    }

    public function testShowTopicWhenAuthorized()
    {
        $request = $this->get('/v1/topics/' . $this->topic->id, ['Accept' => 'application/json']);

        $request->assertForbidden();

        $this->actingAs($this->user, 'api');

        $request = $this->get('/v1/topics/' . $this->topic->id, ['Accept' => 'application/json']);

        $request->assertJsonFragment([
            'subject' => $this->topic->subject,
            'read_at' => null,
            'is_read' => true,
            'forum' => [
                'id' => $this->forum->id,
                'name' => $this->forum->name,
                'slug' => $this->forum->slug
            ]
        ]);
    }

    public function testMarkAsRead()
    {
        $this->actingAs($this->user, 'api');

        Tracker::make($this->topic, $this->user->guest_id)->asRead($this->topic->last_post_created_at);

        $request = $this->get('/v1/topics/' . $this->topic->id, ['Accept' => 'application/json']);
        $data = $request->decodeResponseJson();

        $this->assertTrue($data['is_read']);
    }

    public function testShowAllTopicsWithMarkedAsRead()
    {
        $this->actingAs($this->user, 'api');

        Tracker::make($this->topic, $this->user->guest_id)->asRead($this->topic->last_post_created_at);

        $request = $this->get('/v1/topics', ['Accept' => 'application/json']);
        $data = $request->decodeResponseJson('data');

        $this->assertEquals($this->topic->subject, $data[0]['subject']);
        $this->assertTrue($data[0]['is_read']);
    }

    public function testShowTopicAsNew()
    {
        Guest::forceCreate(['id' => $this->user->guest_id, 'updated_at' => now()->subMinute(5)]);

        $this->actingAs($this->user, 'api');

        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);
        factory(Post::class)->create(['topic_id' => $topic->id, 'forum_id' => $this->forum->id, 'created_at' => now()]);

        $request = $this->get('/v1/topics/' . $this->topic->id, ['Accept' => 'application/json']);
        $data = $request->decodeResponseJson();

        $this->assertFalse($data['is_read']);
    }
}
