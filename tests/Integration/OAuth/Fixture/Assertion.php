<?php
namespace Tests\Integration\OAuth\Fixture;

use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Constraint\Constraint;
use Tests\Integration\BaseFixture\Server;
use Tests\Integration\OAuth\Fixture\Constraint\ResponseRedirect;

trait Assertion
{
    use Server\Http;

    function oAuthLogin(string $provider): TestResponse
    {
        return $this->server->get("/OAuth/$provider/Login");
    }

    function redirect(Constraint $constraint): Constraint
    {
        return new ResponseRedirect($constraint);
    }
}
