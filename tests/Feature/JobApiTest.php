<?php

namespace Tests\Feature;

use Coyote\Coupon;
use Coyote\Firm;
use Coyote\Job;
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
            'title' => $this->faker->text(60),
            'salary_from' => 3000,
            'salary_to' => 5000,
            'rate' => 'weekly',
            'currency' => 'USD',
            'plan' => 'standard',
            'seniority' => 'lead',
            'employment' => 'mandatory',
            'recruitment' => $this->faker->url,
            'locations' => [
                [
                    'city' => 'Wrocław',
                    'street' => 'Rynek',
                    'street_number' => '23',
                    'country' => 'PL'
                ]
            ]
        ];

        $response = $this->json('POST', '/v1/jobs', $data, ['Authorization' => 'Bearer ' . $this->token, 'Accept' => 'application/json']);
        var_dump($response->getContent());

        $this->assertEquals(201, $response->getStatusCode());
        $response->assertJsonFragment(['title' => $data['title'], 'currency' => 'USD']);

        $this->assertNotNull(Coupon::withTrashed()->find($coupon->id)->deleted_at);

        $result = json_decode($response->getContent(), true);
        $job = Job::find($result['id']);

        $this->assertFalse($job->enable_apply);
    }

    public function testSubmitWithAlreadyCreatedFirm()
    {
        Coupon::create(['amount' => 57, 'code' => str_random(), 'user_id' => $this->user->id]);
        $firm = factory(Firm::class)->create(['user_id' => $this->user->id]);

        $data = [
            'title' => $this->faker->text(255),
            'firm' => [
                'name' => $firm->name
            ]
        ];

        $response = $this->json('POST', '/v1/jobs', $data, ['Authorization' => 'Bearer ' . $this->token, 'Accept' => 'application/json']);
        $this->assertEquals(201, $response->getStatusCode());

        $result = json_decode($response->getContent(), true);

        $this->assertEquals($result['firm']['name'], $firm->name);
        $this->assertEquals($result['firm']['website'], $firm->website);
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
