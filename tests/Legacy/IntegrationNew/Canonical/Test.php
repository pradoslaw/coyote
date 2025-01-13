<?php
namespace Tests\Legacy\IntegrationNew\Canonical;

use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\Canonical;

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
