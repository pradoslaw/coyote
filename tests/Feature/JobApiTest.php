<?php

namespace Tests\Feature;

use Coyote\Coupon;
use Coyote\User;
use Faker\Factory;
use Tests\TestCase;

class JobApiTest extends TestCase
{
    private $user;
    private $token;
    private $faker;

    public function setUp()
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->token = $this->user->createToken('4programmers.net')->accessToken;

        $this->faker = Factory::create();
    }

    public function testSubmitSuccessful()
    {
        $coupon = Coupon::create(['amount' => 30, 'code' => str_random(), 'user_id' => $this->user->id]);

        $data = [
            'title' => $this->faker->title,
            'salary_from' => 3000,
            'salary_to' => 5000,
            'salary_rate' => 'weekly',
            'currency' => 'USD',
            'plan' => 'standard',
            'seniority' => 'lead',
            'employment' => 'mandatory'
        ];

        $response = $this->json('POST', '/v1/jobs', $data, ['Authorization' => 'Bearer ' . $this->token, 'Accept' => 'application/json']);

        $this->assertEquals(201, $response->getStatusCode());
        $response->assertJsonFragment(['title' => $data['title'], 'currency' => 'USD', 'currency_symbol' => '$']);

        $this->assertNotNull(Coupon::withTrashed()->find($coupon->id)->deleted_at);
    }

    public function testNotEnoughFunds()
    {
        $data = [
            'title' => $this->faker->title
        ];

        $response = $this->json('POST', '/v1/jobs', $data, ['Authorization' => 'Bearer ' . $this->token, 'Accept' => 'application/json']);

        $this->assertEquals(403, $response->getStatusCode());
        $response->assertJsonFragment(['message' => 'No sufficient funds to post this job offer.']);
    }
}
