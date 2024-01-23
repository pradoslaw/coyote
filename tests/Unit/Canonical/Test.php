<?php
namespace Tests\Unit\Canonical;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Canonical;

class Test extends TestCase
{
    use Canonical\Fixture\Assertion;

    /**
     * @test
     */
    public function emptyQueryParams()
    {
        $this->assertRedirectGet('/Forum?', '/Forum');
    }
}
