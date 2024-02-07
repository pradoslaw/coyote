<?php
namespace Tests\Unit\WhatsNew\Fixture;

use PHPUnit\Framework\Constraint\Constraint;
use SebastianBergmann\Comparator\ComparisonFailure;

class TrimmedString extends Constraint
{
    public function __construct(private string $value)
    {
    }

    public function evaluate($other, string $description = '', bool $returnResult = false): ?bool
    {
        $success = $this->value === \trim($other);
        if ($returnResult) {
            return $success;
        }
        if (!$success) {
            $this->fail($other, $description, new ComparisonFailure(
                $this->value,
                $other,
                "'$this->value'",
                "'$other'",
            ));
        }
        return null;
    }

    public function toString(): string
    {
        return 'is identical to trimmed ' . $this->exporter()->export($this->value);
    }

    protected function failureDescription($other): string
    {
        if (\is_string($other)) {
            return 'two strings are identical';
        }
        return parent::failureDescription($other);
    }
}
