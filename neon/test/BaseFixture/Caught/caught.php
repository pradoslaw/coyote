<?php
namespace Neon\Test\BaseFixture\Caught;

use PHPUnit\Framework\AssertionFailedError;

function caught(callable $block): \Throwable
{
    try {
        $block();
    } catch (\Throwable $throwable) {
        return $throwable;
    }
    throw new AssertionFailedError('Failed to assert that exception is thrown.');
}
