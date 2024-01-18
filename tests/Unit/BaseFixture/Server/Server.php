<?php
namespace Tests\Unit\BaseFixture\Server;

use Illuminate\Testing\TestResponse;

class Server
{
    public string $baseUrl;

    public function __construct(private Laravel\TestCase $laravel)
    {
        $this->baseUrl = 'http://4programmers.local';
    }

    public function get(string $uri): TestResponse
    {
        return $this->laravel->get($this->baseUrl . $uri);
    }
}
