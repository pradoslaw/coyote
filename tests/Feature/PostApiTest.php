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
    }

    public function testShowPostWhenAuthorized()
    {
        $request = $this->get('/v1/posts/' . $this->post->id, ['Accept' => 'application/json']);

        $request->assertForbidden();

        $this->actingAs($this->user, 'api');

        $request = $this->get('/v1/posts/' . $this->post->id, ['Accept' => 'application/json']);

        $request->assertJsonFragment([
            'user_name' => $this->post->user_name,
            'topic_id' => $this->topic->id,
            'forum_id' => $this->forum->id
        ]);
    }
}
