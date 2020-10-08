<?php

namespace Tests\Feature\Policies;

use Coyote\Forum;
use Coyote\Policies\PostPolicy;
use Coyote\Post;
use Coyote\Topic;
use Coyote\User;
use Faker\Factory;
use Tests\TestCase;

class PostPolicyTest extends TestCase
{
    /**
     * @var Forum
     */
    private $forum;

    /**
     * @var Topic
     */
    private $topic;

    /**
     * @var Post
     */
    private $post;

    /**
     * @var User
     */
    private $user;

    /**
     * @var \Faker\Generator
     */
    private $faker;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        $this->forum = factory(Forum::class)->make();
        $this->topic = factory(Topic::class)->make(['forum_id' => $this->forum->id]);

        $this->user = factory(User::class)->make(['id' => $this->faker->numberBetween()]);

        /** @var Post $post */
        $this->post = factory(Post::class)->make(['id' => $this->faker->numberBetween(), 'user_id' => $this->user->id]);

        $this->post->topic()->associate($this->topic);
        $this->post->forum()->associate($this->forum);
    }

    public function testDeleteAndRestoreLastPostByAuthor()
    {
        $this->topic->last_post_id = $this->post->id; // last post in topic

        $policy = new PostPolicy();
        $this->assertTrue($policy->delete($this->user, $this->post));

        $this->post->deleted_at = now();

        $this->assertTrue($policy->delete($this->user, $this->post));
    }

    public function testDeleteAndRestoreLastPostByAuthorDespiteAllowedTime()
    {
        $this->topic->last_post_id = $this->post->id; // last post in topic

        $this->post->created_at = now()->subMinutes(31);

        $policy = new PostPolicy();
        $this->assertTrue($policy->delete($this->user, $this->post));
    }

    public function testDeleteAndRestorePostByAuthor()
    {
        $post = factory(Post::class)->make(['id' => $this->faker->numberBetween()]);
        $this->topic->last_post_id = $post->id; // last post in topic

        $policy = new PostPolicy();
        $this->assertTrue($policy->delete($this->user, $this->post));

        $this->post->deleted_at = now();

        $this->assertTrue($policy->delete($this->user, $this->post));
    }

    public function testDeleteNotAllowedDueToTimeOfCreation()
    {
        $post = factory(Post::class)->make(['id' => $this->faker->numberBetween()]);
        $this->topic->last_post_id = $post->id; // last post in topic

        $this->post->created_at = now()->subMinutes(31);

        $policy = new PostPolicy();
        $this->assertFalse($policy->delete($this->user, $this->post));
    }
}
