<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginTest extends TestCase
{
    public function testApiLogin()
    {
        $response = $this->json('POST', '/v1/login', ['name' => 'admin', 'password' => '1234']);
        $response->assertJsonValidationErrors(['name']);

        $response = $this->json('POST', '/v1/login', ['name' => 'admin', 'password' => '123']);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
