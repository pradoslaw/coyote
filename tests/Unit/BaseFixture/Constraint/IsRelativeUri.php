<?php
namespace Tests\Unit\BaseFixture\Constraint;

use PHPUnit\Framework\Constraint\Constraint;

class IsRelativeUri extends Constraint
{
    public function __construct(
        private string $relativeUri,
        private string $baseUrl)
    {
    }

    protected function matches($other): bool
    {
        return $this->relativeUri === $this->relativeUri($other);
    }

    private function relativeUri(string $url): string
    {
        if (\str_starts_with($url, $this->baseUrl)) {
            return \subStr($url, \strLen($this->baseUrl));
        }
        throw new \AssertionError("Failed to assert that uri $url starts with $this->baseUrl");
    }

    public function toString(): string
    {
        return "has relative uri '$this->relativeUri'";
    }
}
