<?php
namespace Tests\Unit\Canonical\Topic\Fixture\PostEdit;

use Coyote\User;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Assert;
use Tests\Unit\BaseFixture;

trait Assertion
{
    use BaseFixture\Server\RelativeUri;

    function assertCanonicalPostEdit(User $author, string $uri): void
    {
        $this->request($uri, $author)
            ->assertSuccessful()
            ->assertStatus(200);
    }

    function assertRedirectPostEdit(User $author, string $uri, string $expectedRedirect, int $status): void
    {
        $response = $this->request($uri, $author)
            ->assertRedirect()
            ->assertStatus($status);
        Assert::assertThat(
            $response->headers->get('Location'),
            $this->relativeUri($expectedRedirect));
    }

    function request(string $uri, User $author): TestResponse
    {
        return $this->server->postJson($uri,
            body:['title' => 'irrelevant irrelevant irrelevant', 'text' => 'irrelevant'],
            user:$author);
    }
}
