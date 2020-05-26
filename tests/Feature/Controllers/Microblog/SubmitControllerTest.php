<?php

namespace Tests\Feature\Controllers\Microblog;

use Coyote\Microblog;
use Coyote\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SubmitControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testSubmitEmpty()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->json('POST', '/Mikroblogi/Edit');
        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['text']);
    }

    public function testSubmitUnauthenticated()
    {
        $response = $this->json('POST', '/Mikroblogi/Edit');
        $response->assertStatus(401);

        $response->assertJson(['message' => 'Unauthenticated.']);
    }

    public function testSubmitValid()
    {
        $fake = Factory::create();
        $user = factory(User::class)->create();
        $text = $fake->realText();

        $this->actingAs($user)->json('POST', '/Mikroblogi/Edit', ['text' => $text]);
        $this->assertDatabaseHas('microblogs', ['text' => $text]);

        $after = clone $user;
        $after->refresh();

        $this->assertGreaterThan($user->reputation, $after->reputation);
    }

    public function testUpdate()
    {
        $fake = Factory::create();

        $microblog = factory(Microblog::class)->create();
        $microblog->load(['user' => function ($builder) {
            return $builder->select();
        }]);

        $response = $this->actingAs($microblog->user)->json('POST', '/Mikroblogi/Edit/' . $microblog->id, ['text' => $text = $fake->text]);
        $response->assertStatus(200);

        $response->assertJsonFragment(['text' => $text]);
    }

    public function testUpdateAndThrowUnauthorized()
    {
        $fake = Factory::create();

        $microblog = factory(Microblog::class)->create();
        $microblog->load(['user' => function ($builder) {
            return $builder->select();
        }]);

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->json('POST', '/Mikroblogi/Edit/' . $microblog->id, ['text' => $text = $fake->text]);
        $response->assertStatus(403);

        $response->assertJson(['message' => 'This action is unauthorized.']);
    }
}
