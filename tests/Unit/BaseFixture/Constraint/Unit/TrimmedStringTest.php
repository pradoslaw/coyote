<?php
namespace Tests\Unit\BaseFixture\Constraint\Unit;

use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture\Constraint;
use Tests\Unit\BaseFixture\Constraint\TrimmedString;

class TrimmedStringTest extends TestCase
{
    use Constraint\Unit\Fixture\Assertion;

    public function test()
    {
        $this->assertAccepts(new TrimmedString('foo'), '  foo  ');
    }

    /**
     * @test
     */
    public function rejectString()
    {
        $this->assertRejects(new TrimmedString('bar'), '  foo  ');
    }

    /**
     * @test
     */
    public function rejectInteger()
    {
        $this->assertRejects(new TrimmedString('14'), 14);
    }

    /**
     * @test
     */
    public function message()
    {
        $this->assertRejectsMessage(
            new TrimmedString('bar'),
            '  foo  ',
            "Failed asserting that '  foo  ' trimmed is 'bar'.");
    }

    /**
     * @test
     */
    public function comparison()
    {
        $this->assertRejectsCompare(
            new TrimmedString('bar'),
            '  foo  ',
            "'bar'",
            "'  foo  '");
    }
}
