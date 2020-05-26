<?php

namespace Tests\Feature\Controllers\Microblog;

use Coyote\Microblog;
use Coyote\Notifications\Microblog\VotedNotification;
use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class VoteControllerTest extends TestCase
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

    public function testFailedVote()
    {
        $microblog = factory(Microblog::class)->create();
        $response = $this->json('POST', '/Mikroblogi/Vote/' . $microblog->id);

        $response->assertJson(['message' => 'Musisz być zalogowany, aby oddać ten głos.']);
    }

    public function testSuccessfulVote()
    {
        Notification::fake();
        $microblog = factory(Microblog::class)->create();

        $response = $this->actingAs($this->user)->json('POST', '/Mikroblogi/Vote/' . $microblog->id);

        Notification::assertSentTo(
            [$microblog->user], VotedNotification::class
        );

        $response->assertSee(1);

        $response = $this->actingAs($this->user)->json('POST', '/Mikroblogi/Vote/' . $microblog->id);
        $response->assertSee(0);
    }
}
