<?php

namespace Tests\Feature;

use Coyote\Microblog;
use Coyote\User;
use DateTime;
use Tests\TestCase;
use Faker\Factory;

class MicroblogTest extends TestCase
{
    public function testSubmitEmpty()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->json('POST', '/Mikroblogi/Edit', ['text' => '']);
        $response->assertStatus(422);
    }

    public function testSubmitForm()
    {
        $fake = Factory::create();
        $user = factory(User::class)->create();
        $text = $fake->realText();

        $this->actingAs($user)->json('POST', '/Mikroblogi/Edit', ['text' => $text]);
        $this->assertDatabaseHas('microblogs', ['text' => $text]);

        $after = User::find($user->id);
        $this->assertGreaterThan($user->reputation, $after->reputation);
    }

    public function testEditForm()
    {
        $fake = Factory::create();
        $user = factory(User::class)->create();

        $microblog = Microblog::forceCreate([
            'user_id' => $user->id,
            'text' => $text = $fake->text,
            'created_at' => new DateTime(),
            'updated_at' => new DateTime(),
            'score' => 0
        ]);

        $response = $this->actingAs($user)->get('/Mikroblogi/Edit/' . $microblog->id);

        $response->assertSeeText($text);
    }

    public function testSimpleRequest()
    {
        $microblog = factory(Microblog::class)->create();
        $response = $this->json('GET', '/v1/microblogs');

        $response
            ->assertStatus(200)
            ->assertSeeText($microblog->text);
    }
}
