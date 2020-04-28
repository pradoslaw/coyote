<?php

namespace Tests\Feature;

use Coyote\Coupon;
use Coyote\Firm;
use Coyote\Job;
use Coyote\Payment;
use Coyote\User;
use Faker\Factory;
use Tests\TestCase;

class JobApiTest extends TestCase
{
    /**
     * @var User
     */
    private $user;
    private $token;
    private $faker;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->token = $this->user->createToken('4programmers.net')->accessToken;

        $this->faker = Factory::create();
    }

    public function testGetSingleJob()
    {
        $job = factory(Job::class)->create(['user_id' => $this->user->id]);

        $response = $this->get('/v1/jobs/' . $job->id, ['Accept' => 'application/json', 'Content-type' => 'application/json']);

        $this->assertEquals(200, $response->getStatusCode());
        $response->assertJsonFragment([
            'title' => $job->title,
            'salary_from' => $job->salary_from,
            'salary_to' => $job->salary_to
        ]);
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
            'is_gross' => true,
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

        $this->assertEquals(201, $response->getStatusCode());
        $response->assertJsonFragment([
            'title' => $data['title'],
            'currency' => $data['currency'],
            'salary_from' => $data['salary_from'],
            'salary_to' => $data['salary_to'],
            'rate' => $data['rate'],
            'employment' => $data['employment'],
            'seniority' => $data['seniority'],
            'is_gross' => true,
            'is_remote' => false,
            'firm' => null
        ]);

        $this->assertNotNull(Coupon::withTrashed()->find($coupon->id)->deleted_at);

        $result = json_decode($response->getContent(), true);
        /** @var Job $job */
        $job = Job::find($result['id']);

        $this->assertFalse($job->enable_apply);
        $this->assertEquals($job->seniority, $data['seniority']);
        $this->assertEquals($job->employment, $data['employment']);
        $this->assertEquals($job->rate, $data['rate']);
        $this->assertTrue($job->is_publish);

        $payment = $job->payments->first();
        $this->assertEquals(Payment::PAID, $payment->status_id);
    }

    public function testSubmitWithFirm()
    {
        Coupon::create(['amount' => 30, 'code' => str_random(), 'user_id' => $this->user->id]);

        $data = [
            'title' => $this->faker->text(60),
            'plan' => 'standard',
            'firm' => [
                'name' => $this->faker->company
            ]
        ];

        $response = $this->json('POST', '/v1/jobs', $data, ['Authorization' => 'Bearer ' . $this->token, 'Accept' => 'application/json']);
        $this->assertEquals(201, $response->getStatusCode());

        $response->assertJsonFragment([
            'title' => $data['title'],
            'currency' => 'PLN',
            'salary_from' => null,
            'salary_to' => null,
            'rate' => 'monthly',
            'employment' => 'employment',
            'seniority' => null,
            'is_gross' => false,
            'is_remote' => false
        ]);
    }

    public function testSubmitWithAlreadyCreatedFirm()
    {
        Coupon::create(['amount' => 57, 'code' => str_random(), 'user_id' => $this->user->id]);
        $firm = factory(Firm::class)->create(['user_id' => $this->user->id]);

        $data = [
            'title' => $this->faker->text(60),
            'tags' => [
                ['name' => 'php']
            ],
            'firm' => [
                'name' => $firm->name
            ]
        ];

        $response = $this->json('POST', '/v1/jobs', $data, ['Authorization' => 'Bearer ' . $this->token, 'Accept' => 'application/json']);
        $this->assertEquals(201, $response->getStatusCode());

        $result = json_decode($response->getContent(), true);

        $this->assertEquals($result['firm']['name'], $firm->name);
        $this->assertEquals($result['firm']['website'], $firm->website);
        $this->assertEquals($result['tags'][0]['name'], 'php');

        $data = [
            'title' => $data['title']
        ];

        $response = $this->json('PUT', '/v1/jobs/' . $result['id'], $data, ['Authorization' => 'Bearer ' . $this->token, 'Accept' => 'application/json']);
        $this->assertEquals(200, $response->getStatusCode());

        $response->assertJsonFragment(['title' => $data['title'], 'firm' => null, 'tags' => []]);
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
