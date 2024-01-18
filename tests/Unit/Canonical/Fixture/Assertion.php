<?php
namespace Tests\Unit\Canonical\Fixture;

use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Assert;
use Tests\Unit\BaseFixture\Server;

trait Assertion
{
    use Server\Http, Server\RelativeUri;

    function assertCanonical(TestResponse $response): void
    {
        $response->assertStatus(200);
    }

    function assertRedirect(TestResponse $response, string $location, int $status): void
    {
        $response->assertRedirect();
        $this->assertRelativeUri($location, $response->headers->get('Location'));
        $this->assertStatusCode($status, $response->getStatusCode());
    }

    function get(string $requestUri): TestResponse
    {
        return $this->server->get($requestUri);
    }

    function post(string $requestUri): TestResponse
    {
        return $this->server->post($requestUri);
    }

    function assertRelativeUri(string $expected, string $actual): void
    {
        Assert::assertThat($actual, $this->relativeUri($expected),
            "Failed asserting redirect to: $expected (instead redirected to $actual).");
    }

    function assertStatusCode(int $expected, int $actual): void
    {
        Assert::assertSame($expected, $actual,
            "Failed asserting redirect returns status code [$expected] (actually was [$actual]).");
    }
}
