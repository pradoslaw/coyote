<?php

namespace Tests\Unit\Models;

use Carbon\Carbon;
use Coyote\Pm;
use Coyote\Repositories\Contracts\PmRepositoryInterface;
use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Faker\Factory;

class PmTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private $author;
    private $repo;

    public function setUp(): void
    {
        parent::setUp();

        $this->repo = app(PmRepositoryInterface::class);
        $this->user = factory(User::class)->create();
        $this->author = factory(User::class)->create();
    }

    public function testPrivateMessageCreation()
    {
        $this->repo->submit($this->user, ['text' => 'Lorem ipsum lores', 'author_id' => $this->author->id]);

        $this->assertDatabaseHas('pm', ['author_id' => $this->author->id]);
        $after = User::find($this->author->id);

        $this->assertEquals($this->author->pm + 1, $after->pm);
        $this->assertEquals($this->author->pm_unread + 1, $after->pm_unread);
    }

    public function testMarkAsRead()
    {
        $pm = $this->repo->submit($this->user, ['text' => 'Lorem ipsum lores', 'author_id' => $this->author->id]);

        $pm[Pm::INBOX]->read_at = Carbon::now();
        $pm[Pm::INBOX]->save();

        $after = User::find($this->author->id);

        $this->assertEquals($this->author->pm + 1, $after->pm);
        $this->assertEquals($this->author->pm_unread, $after->pm_unread);
    }

    public function testPrivateMessageDelete()
    {
        $pm = $this->repo->submit($this->user, ['text' => 'Lorem ipsum lores', 'author_id' => $this->author->id]);
        $pm[Pm::INBOX]->delete();

        $after = User::find($this->author->id);

        $this->assertEquals($this->author->pm, $after->pm);
        $this->assertEquals($this->author->pm_unread, $after->pm_unread);

        // jeden rekord powinien byc w bazie danych...
        $this->assertDatabaseHas('pm', ['user_id' => $this->user->id]);
    }

    public function testCompleteRemovingFromDatabase()
    {
        $pm = $this->repo->submit($this->user, ['text' => 'Lorem ipsum lores', 'author_id' => $this->author->id]);
        $pm[Pm::INBOX]->delete();

        // jeden rekord powinien byc w bazie danych...
        $this->assertDatabaseHas('pm_text', ['id' => $pm[Pm::SENTBOX]->text_id]);

        $pm[Pm::SENTBOX]->delete();
        $this->assertDatabaseMissing('pm_text', ['id' => $pm[Pm::SENTBOX]->text_id]);
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
