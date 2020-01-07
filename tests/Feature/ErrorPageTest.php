<?php

namespace Tests\Feature;

use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    public function testError500()
    {
        $env = app()->environment();

        try {
            $response = $this->get('/');
            $response->assertStatus(200);

            putenv('production');

            $response = $this->get('/'); // should throw error page because dusk is enabled and its not allowed in production env

            $response
                ->assertStatus(500)
                ->assertSeeText('Whoops, looks like something went wrong.');
        } finally {
            putenv($env);
        }

    }
}
