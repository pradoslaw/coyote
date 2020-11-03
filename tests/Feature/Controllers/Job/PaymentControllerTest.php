<?php

namespace Tests\Feature\Controllers\Job;

use Coyote\Job;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    /**
     * @var Job
     */
    private $job;

    public function setUp(): void
    {
        parent::setUp();

        $this->job = factory(Job::class)->create(['is_publish' => false]);
    }

    public function testSubmitInvalidFormWithoutAnyData()
    {
        $response = $this->actingAs($this->job->user)->json('POST', '/Praca/Payment/' . $this->job->getUnpaidPayment()->id);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(
            ['payment_method']
        );
    }

    public function testSubmitInvalidForm()
    {
        $payment = $this->job->getUnpaidPayment();

        $response = $this->actingAs($this->job->user)->json(
            'POST',
            "/Praca/Payment/{$payment->id}",
            ['payment_method' => 'card', 'price' => $payment->plan->gross_price, 'enable_invoice' => true]
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(
            ['name', 'number', 'exp', 'cvc', 'invoice.address', 'invoice.name', 'invoice.city', 'invoice.country_id', 'invoice.postal_code']
        );

        $response->assertJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                'name' => ['Pole nazwa jest wymagane.'],
                'number' => ['Pole numer karty kredytowej jest wymagane.'],
                'cvc' => ['Pole CVC jest wymagane.'],
                'exp' => ['Pole data ważności karty jest wymagane.'],
                'invoice.name' => ['To pole jest wymagane.'],
                'invoice.address' => ['To pole jest wymagane.'],
                'invoice.city' => ['To pole jest wymagane.'],
                'invoice.postal_code' => ['To pole jest wymagane.'],
                'invoice.country_id' => ['To pole jest wymagane.'],

            ]
        ]);
    }
}
