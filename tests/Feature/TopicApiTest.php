<?php

namespace Tests\Feature;

use Coyote\Forum;
use Coyote\Group;
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

        $this->user = factory(User::class)->create();

        /** @var Group $group */
        $group = factory(Group::class)->create();
        $group->users()->attach($this->user->id);

        $this->forum = factory(Forum::class)->create(['is_prohibited' => true]);
        $this->forum->access()->create(['group_id' => $group->id]);
        $this->topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);

        $this->token = $this->user->createToken('4programmers.net')->accessToken;
    }

    public function testShowAllTopics()
    {
        $request = $this->get('/v1/topics', ['Accept' => 'application/json']);

        $data = $request->decodeResponseJson('data');
        $this->assertNotEquals($data[0]['subject'], $this->topic->subject);
    }

    public function testShowAllTopicsAuthorized()
    {
        $request = $this->get('/v1/topics', ['Accept' => 'application/json', 'Authorization' => 'Bearer ' . $this->token]);
        $data = $request->decodeResponseJson('data');

        $this->assertEquals($data[0]['subject'], $this->topic->subject);
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
}
