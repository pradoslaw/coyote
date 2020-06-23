<?php

namespace Tests\Feature\Controllers\Forum;

use Coyote\Forum;
use Coyote\Post;
use Coyote\Topic;
use Coyote\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SubmitControllerTest extends TestCase
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

    public function setUp(): void
    {
        parent::setUp();

        $this->forum = factory(Forum::class)->create();
        $this->user = factory(User::class)->create();
    }

    public function testSubmitTopicWithPost()
    {
        $post = factory(Post::class)->make();
        $faker = Factory::create();

        $response = $this->actingAs($this->user)->json(
            'POST',
            "/Forum/{$this->forum->slug}/Submit",
            ['text' => $post->text, 'subject' => $faker->text(50), 'is_sticky' => true, 'subscribe' => true]
        );

        $response->assertJsonFragment([
            'text' => $post->text
        ]);

        $id = $response->decodeResponseJson('id');

        $this->assertDatabaseHas('posts', ['id' => $id]);
        $this->assertDatabaseHas('topics', ['first_post_id' => $id, 'is_sticky' => false]);

        /** @var Topic $topic */
        $topic = Topic::where('first_post_id', $id)->first();

        $this->assertTrue($topic->subscribers()->forUser($this->user->id)->exists());
    }

    public function testSubmitPostToExistingTopic()
    {
        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);
        $post = factory(Post::class)->make();

        $response = $this->actingAs($this->user)->json('POST', "/Forum/{$this->forum->slug}/Submit/{$topic->id}", ['text' => $post->text]);

        $response->assertJsonFragment([
            'text' => $post->text,
            'is_read' => false,
            'is_locked' => false
        ]);
    }

    public function testEditExistingPostByAuthor()
    {
        $faker = Factory::create();
        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);

        factory(Post::class)->create(['forum_id' => $this->forum->id, 'topic_id' => $topic->id]);
        $post = factory(Post::class)->create(['user_id' => $this->user->id, 'forum_id' => $this->forum->id, 'topic_id' => $topic->id]);

        $response = $this->actingAs($this->user)->json('POST', "/Forum/{$this->forum->slug}/Submit/{$topic->id}/{$post->id}", ['text' => $text = $faker->text]);

        $response->assertJsonFragment([
            'text' => $text
        ]);
    }

    public function testEditExistingPostByAnotherUser()
    {
        $faker = Factory::create();
        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);

        $post = factory(Post::class)->create(['forum_id' => $this->forum->id, 'topic_id' => $topic->id]);

        $response = $this->json('POST', "/Forum/{$this->forum->slug}/Submit/{$topic->id}/{$post->id}", ['text' => $faker->text]);
        $response->assertStatus(403);

        $response = $this->actingAs($this->user)->json('POST', "/Forum/{$this->forum->slug}/Submit/{$topic->id}/{$post->id}", ['text' => $faker->text]);
        $response->assertStatus(403);
    }

    public function testChangeTopicSubject()
    {
        $faker = Factory::create();
        /** @var Topic $topic */
        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);

        $post = $topic->posts()->first();

        $post->user_id = $this->user->id;
        $post->save();

        $response = $this->actingAs($this->user)->json(
            'POST',
            "/Forum/{$this->forum->slug}/Submit/{$topic->id}/{$post->id}",
            ['text' => $text = $faker->text, 'subject' => $subject = $faker->text(100)]
        );

        $response->assertJsonFragment([
            'text' => $text
        ]);

        $topic->refresh();

        $this->assertEquals($subject, $topic->subject);
    }

    public function testTryToChangeTopicSubject()
    {
        $faker = Factory::create();
        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);

        factory(Post::class)->create(['forum_id' => $this->forum->id, 'topic_id' => $topic->id]);
        $post = factory(Post::class)->create(['user_id' => $this->user->id, 'forum_id' => $this->forum->id, 'topic_id' => $topic->id]);

        $this->actingAs($this->user)->json(
            'POST',
            "/Forum/{$this->forum->slug}/Submit/{$topic->id}/{$post->id}",
            ['text' => $text = $faker->text, 'subject' => $subject = $faker->text(100)]
        );

        $topic->refresh();

        $this->assertNotEquals($subject, $topic->subject);
    }

//    public function testEditExistingPostInLockedTopic()
//    {
//
//    }
//
//    public function testEditExistingPostInLockedForum()
//    {
//
//    }

//    public function testSubmitStickyTopic()
//    {
//
//    }
}
