<?php
namespace Tests\Unit\Canonical;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Canonical;

class ScriptFileTest extends TestCase
{
    use Canonical\Fixture\Assertion;

    /**
     * @test
     */
    public function test()
    {
        $this->assertRedirectGet('/index.php', '/');
    }

    /**
     * @test
     */
    public function trailingSlash()
    {
        $this->assertRedirectGet('/index.php/', '/');
    }
}
