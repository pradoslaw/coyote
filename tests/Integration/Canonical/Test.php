<?php
namespace Tests\Integration\Canonical;

use PHPUnit\Framework\TestCase;
use Tests\Integration\Canonical;

class Test extends TestCase
{
    use Canonical\Fixture\Assertion;

    /**
     * @test
     */
    public function emptyQueryParams()
    {
        $this->assertNoRedirectGet('/Forum?');
    }
}
