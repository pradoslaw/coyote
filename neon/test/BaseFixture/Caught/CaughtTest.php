<?php
namespace Neon\Test\BaseFixture\Caught;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

class CaughtTest extends TestCase
{
    /**
     * @test
     */
    public function caught(): void
    {
        $exception = caught(function () {
            throw new \Exception('foo');
        });
        $this->assertSame('foo', $exception->getMessage());
    }

    /**
     * @test
     */
    public function noException(): void
    {
        try {
            caught(function () {
            });
        } catch (AssertionFailedError $exception) {
            $this->assertSame('Failed to assert that exception is thrown.', $exception->getMessage());
        }
    }
}
