<?php
namespace Tests\Unit\Canonical;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Canonical;

class TrailingSlashTest extends TestCase
{
    use Canonical\Fixture\Assertion;

    /**
     * @test
     */
    public function canonical()
    {
        $this->assertCanonical('/Forum');
    }

    /**
     * @test
     */
    public function trailingSlash()
    {
        $this->assertRedirect('/Forum/', '/Forum');
    }
}
