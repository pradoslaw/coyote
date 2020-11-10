<?php

namespace Tests\Feature\Controllers\Job;

use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SubmitControllerTest extends TestCase
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

    public function testSubmitFailsNoTitleWasProvided()
    {
        $response = $this->actingAs($this->user)->json('POST', '/Praca/Submit');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['currency_id', 'plan_id', 'title']);
        $response->assertJsonFragment([
            'message' => 'The given data was invalid.',
            'errors' => [
                'title' => ['Tytuł jest wymagany.'],
                'currency_id' => ['Pole currency id jest wymagane.'],
                'plan_id' => ['Pole plan id jest wymagane.']
            ]
        ]);
    }

    public function testSubmitValidFormWithoutFirm()
    {

    }
}
