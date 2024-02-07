<?php
namespace Tests\Unit\BaseFixture\Constraint\Unit\Fixture;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;

trait Assertion
{
    function assertAccepts(Constraint $constraint, mixed $actual): void
    {
        Assert::assertThat($actual, $constraint);
    }

    function assertRejects(
        Constraint $constraint,
        mixed      $argument,
        string     $failMessage,
        ?string    $failExpected,
        ?string    $failActual): void
    {
        try {
            Assert::assertThat($argument, $constraint);
            throw new \AssertionError('Failed to assert that constraint was not matched');
        } catch (ExpectationFailedException $exception) {
            $comparison = $exception->getComparisonFailure();
            Assert::assertSame(
                ['message' => $failMessage, 'expected' => $failExpected, 'actual' => $failActual],
                [
                    'message'  => $exception->getMessage(),
                    'expected' => $comparison?->getExpectedAsString(),
                    'actual'   => $comparison?->getActualAsString(),
                ]);
        }
    }
}
