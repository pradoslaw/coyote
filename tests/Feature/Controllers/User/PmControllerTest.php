<?php

namespace Tests\Feature\Controllers\User;

use Coyote\User;
use Faker\Factory;
use Tests\TestCase;

class PmControllerTest extends TestCase
{
    private $user;
    private $author;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->author = factory(User::class)->create();
    }

    public function testWriteMessage()
    {
        $faker = Factory::create();

        $response = $this->actingAs($this->user)->post(
            '/User/Pm/Submit',
            ['text' => $text = $faker->text, 'recipient' => $this->author->name],
            ['Accept' => 'application/json']);

        $response
            ->assertStatus(201)
            ->assertSeeText($text);
    }

    public function testWriteMessageWithoutRecipient()
    {
        $faker = Factory::create();

        $response = $this->actingAs($this->user)->post('/User/Pm/Submit', ['text' => $text = $faker->text], ['Accept' => 'application/json']);

        $response
            ->assertJsonValidationErrors(['recipient']);
    }

    public function testWriteMessageToMyself()
    {
        $faker = Factory::create();

        $response = $this->actingAs($this->user)->post('/User/Pm/Submit', ['text' => $text = $faker->text, 'recipient' => $this->user->name], ['Accept' => 'application/json']);

        $response
            ->assertJsonValidationErrors(['recipient']);
    }
}
