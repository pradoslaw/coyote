<?php
namespace Tests\Unit\BaseFixture\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use SebastianBergmann\Comparator\ComparisonFailure;

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

    private function relativeUri(string $url): ?string
    {
        if (\str_starts_with($url, $this->baseUrl)) {
            return \subStr($url, \strLen($this->baseUrl));
        }
        return null;
    }

    public function toString(): string
    {
        return "has relative uri '$this->relativeUri'";
    }

    protected function fail($other, $description, ComparisonFailure $comparisonFailure = null): void
    {
        parent::fail($other, $description,
            new ComparisonFailure(null, null,
                $this->exporter()->export($this->relativeUri),
                $this->exporter()->export($other)));
    }
}
