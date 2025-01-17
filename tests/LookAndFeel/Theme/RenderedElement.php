<?php
namespace Tests\LookAndFeel\Theme;

use PHPUnit\Framework\Assert;

readonly class RenderedElement
{
    public function __construct(private string $actualValue) {}

    public function is(string $expectedValue): void
    {
        Assert::assertSame($expectedValue, $this->actualValue);
    }
}
