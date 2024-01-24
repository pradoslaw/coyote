<?php
namespace Tests\Unit\BaseFixture\Server;

use Coyote\User;
use Illuminate\Testing\TestResponse;

class Server
{
    private string $hostname;
    public string $baseUrl;

    public function __construct(private Laravel\TestCase $laravel)
    {
        $this->hostname = '4programmers.local';
        $this->baseUrl = "http://$this->hostname";
    }

    public function get(string $uri): TestResponse
    {
        return $this->call('GET', $uri);
    }

    public function call(string $method, string $uri): TestResponse
    {
        return $this->laravel->call(
            method:$method,
            uri:new Url($this->hostname, $uri),
            server:['SCRIPT_FILENAME' => 'index.php'],
        );
    }

    public function postJson(string $uri, array $body, User $user): TestResponse
    {
        $this->laravel->actingAs($user);
        return $this->laravel->json(
            method:'POST',
            uri:new Url($this->hostname, $uri),
            data:$body);
    }
}
