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

        $bearer = $response->getContent();

        $this->json('GET', '/v1/user')->assertStatus(401);

        $response = $this->json('GET', '/v1/user', [], ['Authorization' => 'Bearer ' . $bearer]);
        $response->assertJson(['name' => 'admin']);
    }
}
