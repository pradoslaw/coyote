<?php
namespace Tests\Unit\BaseFixture\Constraint\Unit;

use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture\Constraint;
use Tests\Unit\BaseFixture\Constraint\IsRelativeUri;

class IsRelativeUriTest extends TestCase
{
    use Constraint\Unit\Fixture\Assertion;

    public function test()
    {
        $this->assertAccepts(
            new IsRelativeUri('/abc/def', 'http://host'),
            'http://host/abc/def');
    }

    /**
     * @test
     */
    public function rejectPath()
    {
        $this->assertRejectsCompare(
            new IsRelativeUri('/foo', 'http://host'),
            'http://host/123',
            "Failed asserting that 'http://host/123' has relative uri '/foo'.",
            "'/foo'",
            "'http://host/123'");
    }

    /**
     * @test
     */
    public function rejectHost()
    {
        $this->assertRejectsCompare(
            new IsRelativeUri('/abc', 'http://host'),
            'http://other/abc',
            "Failed asserting that 'http://other/abc' has relative uri '/abc'.",
            "'/abc'",
            "'http://other/abc'");
    }

    /**
     * @test
     */
    public function rejectInteger()
    {
        $this->assertRejectsCompare(
            new IsRelativeUri('/', 'http://host'),
            2,
            "Failed asserting that 2 has relative uri '/'.",
            "'/'",
            '2');
    }
}
