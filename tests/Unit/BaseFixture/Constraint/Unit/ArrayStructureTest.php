<?php
namespace Tests\Unit\BaseFixture\Constraint\Unit;

use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture\Constraint;
use Tests\Unit\BaseFixture\Constraint\ArrayStructure;
use Tests\Unit\BaseFixture\Constraint\IsRelativeUri;
use Tests\Unit\BaseFixture\Constraint\TrimmedString;

class ArrayStructureTest extends TestCase
{
    use Constraint\Unit\Fixture\Assertion;

    public function test()
    {
        $constraint = new ArrayStructure([
            'other' => $this->identicalTo('value'),
            'key'   => new TrimmedString('foo'),
        ]);

        $this->assertAccepts($constraint, ['key' => '  foo  ', 'other' => 'value']);
    }

    /**
     * @test
     */
    public function rejectAll()
    {
        $constraint = new ArrayStructure([
            'key' => 'value', 'missing' => 'missing',
        ]);

        $this->assertRejects($constraint, ['key' => 'value']);
    }

    /**
     * @test
     */
    public function rejectMessageIdentity()
    {
        $constraint = new ArrayStructure(['key' => 'value']);

        $this->assertRejectMessage($constraint, ['other' => 'other'],
            "Failed asserting that value at 'key' is identical to 'value'.");
    }

    /**
     * @test
     */
    public function rejectMessageIdenticalTo()
    {
        $constraint = new ArrayStructure(['key' => $this->identicalTo('value')]);

        $this->assertRejectMessage($constraint, ['other' => 'other'],
            "Failed asserting that value at 'key' is identical to 'value'.");
    }

    /**
     * @test
     */
    public function rejectMessageTrimmed()
    {
        $constraint = new ArrayStructure(['key' => new TrimmedString('value')]);

        $this->assertRejectMessage($constraint, ['foo' => '  bar  '],
            "Failed asserting that value at 'key' trimmed is 'value'.");
    }

    /**
     * @test
     */
    public function rejectMessageRelativeUri()
    {
        $constraint = new ArrayStructure(['key' => new IsRelativeUri('/foo', 'host')]);

        $this->assertRejectMessage($constraint, ['other' => '/bar'],
            "Failed asserting that value at 'key' has relative uri '/foo'.");
    }

    /**
     * @test
     */
    public function rejectMessageOnlyFailed()
    {
        $constraint = new ArrayStructure([
            'one'   => '111',
            'two'   => '222',
            'three' => '333',
            'four'  => '444',
        ]);

        $this->assertRejectMessage($constraint,
            ['two' => '222', 'four' => '444'],
            "Failed asserting that value at 'one' is identical to '111' and value at 'three' is identical to '333'.");
    }

    /**
     * @test
     */
    public function rejectLogicalAnd()
    {
        $constraint = new ArrayStructure([
            'key' => 'value',
            'foo' => 'bar',
        ]);

        $this->assertRejectMessage($this->logicalAnd($constraint), ['key' => 'other'],
            "Failed asserting that Array &0 (
    'key' => 'other'
) value at 'key' is identical to 'value' and value at 'foo' is identical to 'bar'.");
    }

    /**
     * @test
     */
    public function rejectCompareActual()
    {
        $constraint = new ArrayStructure(['key' => 'value']);

        $this->assertRejectsActual(
            $constraint,
            ['one' => '11x1', 'two' => '222', 'three' => '333'],
            "Array &0 (
    'one' => '11x1'
    'two' => '222'
    'three' => '333'
)");
    }

    /**
     * @test
     */
    public function rejectCompareExpectedIdentity()
    {
        $constraint = new ArrayStructure([
            'one'   => '111',
            'two'   => '222',
            'three' => '333',
        ]);

        $this->assertRejectsExpected($constraint, [], "Array &0 (
    'one' => '111'
    'two' => '222'
    'three' => '333'
)");
    }

    /**
     * @test
     */
    public function rejectCompareExpectedMany()
    {
        $constraint = new ArrayStructure([
            'one'   => '111',
            'two'   => $this->identicalTo('222'),
            'three' => new TrimmedString('333'),
            'four'  => new IsRelativeUri('444', 'host'),
        ]);

        $this->assertRejectsExpected($constraint, [], "Array &0 (
    'one' => '111'
    'two' => '222'
    'three' => trimmed is '333'
    'four' => has relative uri '444'
)");
    }

    /**
     * @test
     */
    public function rejectCompareExpectedArgumentOrder()
    {
        $constraint = new ArrayStructure([
            'one'   => '111',
            'two'   => $this->identicalTo('222'),
            'three' => new IsRelativeUri('333', 'host'),
            'four'  => new TrimmedString('444'),
        ]);

        $argument = [
            'two'  => '2',
            'four' => '3',
            'one'  => '1',
            'five' => '5',
        ];

        $this->assertRejectsExpected($constraint, $argument, "Array &0 (
    'two' => '222'
    'four' => trimmed is '444'
    'one' => '111'
    'three' => has relative uri '333'
)");
    }
}
