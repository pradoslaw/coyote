<?php

namespace Tests\Feature\Controllers\Job;

use Coyote\Currency;
use Coyote\Job;
use Coyote\Plan;
use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SubmitControllerTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

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

    public function testSubmitFailsNoEmailWasProvided()
    {
        $response = $this->actingAs($this->user)->json('POST', '/Praca/Submit', [
            'title' => $this->faker->text(60),
            'plan_id' => Plan::first()->id,
            'currency_id' => Currency::first()->id,
            'email' => '',
            'enable_apply' => true
        ]);

        $response->assertJsonValidationErrors(['email']);
        $response->assertJsonFragment([
            'message' => 'The given data was invalid.',
            'errors' => [
                'email' => ['Pole email jest wymagane.']
            ]
        ]);
    }

    public function testSubmitFailsNoRecruitmentWasProvided()
    {
        $response = $this->actingAs($this->user)->json('POST', '/Praca/Submit', [
            'title' => $this->faker->text(60),
            'plan_id' => Plan::first()->id,
            'currency_id' => Currency::first()->id,
            'email' => '',
            'enable_apply' => false
        ]);

        $response->assertJsonValidationErrors(['recruitment']);
        $response->assertJsonFragment([
            'message' => 'The given data was invalid.',
            'errors' => [
                'recruitment' => ['Proszę podać informacje w jaki sposób można składać aplikacje na to stanowisko.']
            ]
        ]);
    }

    public function testSubmitValidFormWithoutFirm()
    {
        $response = $this->actingAs($this->user)->json('POST', '/Praca/Submit', [
            'title' => $title = $this->faker->text(60),
            'plan_id' => Plan::first()->id,
            'currency_id' => $currency = Currency::first()->id,
            'enable_apply' => true,
            'email' => $this->user->email,
            'description' => $description = $this->faker->realText(),
            'rate' => 'weekly',
            'employment' => 'b2b',
            'seniority' => 'lead',
            'is_gross' => true,
            'salary_from' => 10000,
            'salary_to' => 20000,
            'locations' => [
                [
                    'city' => 'Wrocław',
                    'street' => 'Rynek',
                    'latitude' => 51,
                    'longitude' => 17
                ]
            ],
            'tags' => [
                [
                    'name' => 'c#',
                    'priority' => 2
                ]
            ]
        ]);

        $response->assertStatus(200);

        /** @var Job $job */
        $job = Job::where('user_id', $this->user->id)->first();

        $this->assertEquals($title, $job->title);
        $this->assertEquals($description, $job->description);
        $this->assertEquals($currency, $job->currency_id);
        $this->assertEquals('weekly', $job->rate);
        $this->assertEquals('b2b', $job->employment);
        $this->assertEquals('lead', $job->seniority);
        $this->assertEquals(10000, $job->salary_from);
        $this->assertEquals(20000, $job->salary_to);
        $this->assertTrue($job->is_gross);
        $this->assertTrue($job->deadline_at->isFuture());
        $this->assertEquals('Wrocław', $job->locations[0]->city);
        $this->assertEquals('Rynek', $job->locations[0]->street);
        $this->assertEquals(51, $job->locations[0]->latitude);
        $this->assertEquals(17, $job->locations[0]->longitude);
        $this->assertEquals('c#', $job->tags[0]->name);
        $this->assertEquals(2, $job->tags[0]->pivot->priority);
    }
}
