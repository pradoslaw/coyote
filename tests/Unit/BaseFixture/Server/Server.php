<?php
namespace Tests\Unit\BaseFixture\Server;

use Illuminate\Testing\TestResponse;

class Server
{
    public function __construct(private Laravel\TestCase $laravel)
    {
    }

    public function get(string $uri): TestResponse
    {
        return $this->laravel->get($uri);
    }
}
