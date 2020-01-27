<?php

namespace Tests\Feature;

use Coyote\Forum;
use Coyote\Group;
use Coyote\Post;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PostApiTest extends TestCase
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
     * @var Post
     */
    private $post;

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
        $this->post = factory(Post::class)->create(['forum_id' => $this->forum->id, 'topic_id' => $this->topic->id]);

        $this->token = $this->user->createToken('4programmers.net')->accessToken;
    }

    public function testShowAllTopics()
    {
        $request = $this->get('/v1/posts', ['Accept' => 'application/json']);

        $data = $request->decodeResponseJson('data');

        $this->assertNotEquals($data[0]['html'], $this->topic->posts()->first()->html);
    }

    public function testShowAllPostsAuthorized()
    {
        $request = $this->get('/v1/posts', ['Accept' => 'application/json', 'Authorization' => 'Bearer ' . $this->token]);
        $data = $request->decodeResponseJson('data');

        $this->assertEquals($data[0]['html'], $this->topic->posts()->first()->html);
    }

    public function testShowForbiddenWhenUnauthorized()
    {
        $request = $this->get('/v1/posts/' . $this->post->id, ['Accept' => 'application/json']);

        $request->assertForbidden();
    }

    public function testShowPostWhenAuthorized()
    {
        $request = $this->get('/v1/posts/' . $this->post->id, ['Accept' => 'application/json', 'Authorization' => 'Bearer ' . $this->token]);

        $request->assertJsonFragment([
            'user_name' => $this->post->user_name,
            'topic_id' => $this->topic->id,
            'forum_id' => $this->forum->id
        ]);
    }
}
