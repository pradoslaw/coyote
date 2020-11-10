<?php

namespace Tests\Feature\Controllers\Forum;

use Coyote\Forum;
use Coyote\Group;
use Coyote\Post;
use Coyote\Post\Comment;
use Coyote\Topic;
use Coyote\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class CommentControllerTest extends TestCase
{
    use DatabaseTransactions;

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
    protected $faker;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        $this->forum = factory(Forum::class)->create();
        $this->user = $this->createUserWithGroup();

        $this->topic = factory(Topic::class)->create();

        $this->post = factory(Post::class)->create(['forum_id' => $this->forum->id, 'topic_id' => $this->topic->id]);
    }

    public function testSubmitInvalidData()
    {
        $response = $this
            ->actingAs($this->user)
            ->json('POST', "/Forum/Comment");

        $response->assertJsonValidationErrors(['post_id', 'text']);

        $response->assertJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                'text' => ['Proszę wpisać treść.'],
                'post_id' => ['Pole post id jest wymagane.']
            ]
        ]);
    }

    public function testSubmitValidData()
    {
        $response = $this
            ->actingAs($this->user)
            ->json('POST', "/Forum/Comment", ['text' => $text = $this->faker->realText(), 'post_id' => $this->post->id]);

        $response->assertJsonFragment([
            'text' => $text,
            'editable' => true
        ]);

        $id = $response->decodeResponseJson('id');

        $comment = Post\Comment::find($id);

        $this->assertTrue($comment->post->subscribers()->forUser($this->user->id)->exists());
    }

    public function testSubmitAsAnonymousUser()
    {
        $response = $this
            ->json('POST', "/Forum/Comment", ['text' =>  $this->faker->realText(), 'post_id' => $this->post->id]);

        $response->assertStatus(401);
    }

    public function testSubmitInLockedForum()
    {
        $this->forum->is_locked = true;
        $this->forum->save();

        $response = $this
            ->actingAs($this->user)
            ->json('POST', "/Forum/Comment", ['text' => $text = $this->faker->realText(), 'post_id' => $this->post->id]);

        $response->assertStatus(403);
    }

    public function testSubmitInLockedTopic()
    {
        $this->topic->is_locked = true;
        $this->topic->save();

        $response = $this
            ->actingAs($this->user)
            ->json('POST', "/Forum/Comment", ['text' => $text = $this->faker->realText(), 'post_id' => $this->post->id]);

        $response->assertStatus(403);
    }

    public function testSubmitInRestrictedForumIsNotAllowed()
    {
        $group = factory(Group::class)->create();

        $this->forum->is_prohibited = true;
        $this->forum->save();

        $this->forum->access()->create(['group_id' => $group->id]);

        $response = $this
            ->actingAs($this->user)
            ->json('POST', "/Forum/Comment", ['text' => $text = $this->faker->realText(), 'post_id' => $this->post->id]);

        $response->assertStatus(403);
    }

    public function testSubmitInRestrictedForumIsAllowed()
    {
        $group = factory(Group::class)->create();

        $this->forum->is_prohibited = true;
        $this->forum->save();

        $this->forum->access()->create(['group_id' => $group->id]);
        $this->user->groups()->sync([$group->id]);

        $response = $this
            ->actingAs($this->user)
            ->json('POST', "/Forum/Comment", ['text' => $text = $this->faker->realText(), 'post_id' => $this->post->id]);

        $response->assertStatus(201);
    }

    public function testSubmitInLockedForumAsAdmin()
    {
        Gate::define('forum-update', function () {
            return true;
        });

        $this->topic->is_locked = true;
        $this->topic->save();

        $response = $this
            ->actingAs($this->user)
            ->json('POST', "/Forum/Comment", ['text' => $text = $this->faker->realText(), 'post_id' => $this->post->id]);

        $response->assertStatus(201);
    }

    public function testUpdateComment()
    {
        $comment = factory(Comment::class)->create(['post_id' => $this->post->id, 'user_id' => $this->user->id]);

        $response = $this
            ->actingAs($this->user)
            ->json('POST', "/Forum/Comment/$comment->id", ['text' => $text = $this->faker->realText(), 'post_id' => $this->post->id]);

        $response->assertJsonFragment(['text' => $text]);

        $id = $response->decodeResponseJson('id');

        $this->assertEquals($id, $comment->id);
    }

    public function testDeleteCommentIsNotAllowed()
    {
        $user = factory(User::class)->create();
        $comment = factory(Comment::class)->create(['post_id' => $this->post->id, 'user_id' => $user->id]);

        $response = $this
            ->actingAs($this->user)
            ->delete("/Forum/Comment/Delete/$comment->id");

        $response->assertStatus(403);
    }

    public function testDeleteCommentByAuthor()
    {
        $comment = factory(Comment::class)->create(['post_id' => $this->post->id, 'user_id' => $this->user->id]);

        $response = $this
            ->actingAs($this->user)
            ->delete("/Forum/Comment/Delete/$comment->id");

        $response->assertStatus(200);
    }

    public function testShowAllComments()
    {
        $comment = factory(Comment::class)->create(['post_id' => $this->post->id, 'user_id' => $this->user->id]);

        $response = $this->get("/Forum/Comment/Show/{$this->post->id}");

        $response->assertStatus(200);
        $result = $response->decodeResponseJson($comment->id);

        $this->assertEquals($comment->text, $result['text']);
        $this->assertArrayNotHasKey('editable', $result);
    }

    public function testShowAllCommentsByAuthorized()
    {
        $comment = factory(Comment::class)->create(['post_id' => $this->post->id, 'user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get("/Forum/Comment/Show/{$this->post->id}");

        $result = $response->decodeResponseJson($comment->id);

        $this->assertEquals($comment->text, $result['text']);
        $this->assertArrayHasKey('editable', $result);
        $this->assertTrue($result['editable']);
    }
}
