<?php
namespace Tests\Unit\Canonical\Fixture;

use PHPUnit\Framework\Assert;
use Tests\Unit\BaseFixture\Server;

trait Assertion
{
    use Server\Http, Server\RelativeUri;

    function assertCanonical(string $requestUri): void
    {
        $this->server->get($requestUri)->assertStatus(200);
    }

    function assertRedirect(string $requestUri, string $expectedRedirect): void
    {
        $response = $this->server->get($requestUri)->assertRedirect();
        $this->assertRelativeUri($expectedRedirect, $response->headers->get('Location'));
        $this->assertStatusCode(301, $response->getStatusCode(), $requestUri);
    }

    function assertRelativeUri(string $expected, string $actual): void
    {
        Assert::assertThat($actual, $this->relativeUri($expected),
            "Failed asserting redirect to: $expected (instead redirected to $actual).");
    }

    function assertStatusCode(int $expected, int $actual, string $uri): void
    {
        Assert::assertSame($expected, $actual,
            "Failed asserting redirect of '$uri' returns status code [$expected] (actually was [$actual]).");
    }
}
