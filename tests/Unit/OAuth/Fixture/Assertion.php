<?php
namespace Tests\Unit\OAuth\Fixture;

use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Constraint\Constraint;
use Tests\Unit\BaseFixture\Server;
use Tests\Unit\OAuth\Fixture\Constraint\ResponseRedirect;

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
