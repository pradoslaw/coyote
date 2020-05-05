<?php

namespace Tests\Feature;

use Coyote\Microblog;
use Coyote\User;
use Tests\TestCase;
use Faker\Factory;

class MicroblogTest extends TestCase
{
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

    public function testSubmitExisting()
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

    public function testSubmitExistingUnauthorized()
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

    public function testApiPaginate()
    {
        $microblog = factory(Microblog::class)->create();
        $response = $this->json('GET', '/v1/microblogs');

        $response
            ->assertStatus(200)
            ->assertSeeText($microblog->text);
    }

    public function testApView()
    {
        /** @var Microblog $microblog */
        $microblog = factory(Microblog::class)->create();
        $response = $this->json('GET', '/v1/microblogs/' . $microblog->id);

        $response
            ->assertStatus(200)
            ->assertJson(array_merge(
                    array_except($microblog->toArray(), ['user_id', 'score']),
                    [
                        'comments' => [],
                        'media' => [],
                        'created_at' => $microblog->created_at->toIso8601String(),
                        'updated_at' => $microblog->created_at->toIso8601String(),
                        'html' => $microblog->html,
                        'user' => [
                            'id' => $microblog->user->id,
                            'name' => $microblog->user->name,
                            'deleted_at' => null,
                            'is_blocked' => false,
                            'photo' => null
                        ]
                    ]
                )
            );
    }
}
