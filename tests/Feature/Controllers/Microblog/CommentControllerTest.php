<?php

namespace Tests\Feature\Controllers\Microblog;

use Coyote\Microblog;
use Coyote\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CommentControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var User
     */
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
    }

    public function testSubmitCommentAndSubscribe()
    {
        $microblog = factory(Microblog::class)->create();
        $fake = Factory::create();

        $text = $fake->realText();

        $response = $this->actingAs($this->user)->json('POST', '/Mikroblogi/Comment', ['text' => $text, 'parent_id' => $microblog->id]);
        $response->assertStatus(201);

        $this->assertDatabaseHas('microblogs', ['text' => $text, 'parent_id' => $microblog->id]);

        $response->assertJsonStructure([
            'is_subscribed',
            'data' => [
                'editable',
                'votes',
                'html'
            ]
        ]);

        $response->assertJson([
            'is_subscribed' => true,
            'data' => [
                'votes' => 0,
                'editable' => true,
            ]
        ]);
    }

    public function testSubmitAnotherComment()
    {
        $microblog = factory(Microblog::class)->create();
        factory(Microblog::class)->create(['parent_id' => $microblog->id, 'user_id' => $this->user->id]);

        $fake = Factory::create();

        $text = $fake->realText();
        $response = $this->actingAs($this->user)->json('POST', '/Mikroblogi/Comment', ['text' => $text, 'parent_id' => $microblog->id]);

        $response->assertJson([
            'is_subscribed' => false // user has submitted comment before. we subscribe microblog only if it was his first comment
        ]);
    }

    public function testUpdateComment()
    {
        $microblog = factory(Microblog::class)->create();
        $comment = factory(Microblog::class)->create(['parent_id' => $microblog->id, 'user_id' => $this->user->id]);

        $fake = Factory::create();

        $text = $fake->realText();
        $response = $this->actingAs($this->user)->json('POST', '/Mikroblogi/Comment/' . $comment->id, ['text' => $text]);

        $response->assertStatus(200);

        $comment->refresh();

        $response->assertJson([
            'data' => [
                'html' => $comment->html
            ]
        ]);
    }

    public function testUpdateCommentAndThrowUnauthorized()
    {
        $microblog = factory(Microblog::class)->create();
        $comment = factory(Microblog::class)->create(['parent_id' => $microblog->id]);

        $fake = Factory::create();
        $response = $this->actingAs($this->user)->json('POST', '/Mikroblogi/Comment/' . $comment->id, ['text' => $fake->realText()]);

        $response->assertStatus(403);

        $response->assertJson(['message' => 'This action is unauthorized.']);
    }
}
