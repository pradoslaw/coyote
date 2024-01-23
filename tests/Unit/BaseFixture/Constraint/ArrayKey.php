<?php
namespace Tests\Unit\BaseFixture\Constraint;

use PHPUnit\Framework\Constraint\ArrayHasKey;
use PHPUnit\Framework\Constraint\Constraint;

class ArrayKey extends Constraint
{
    private ArrayHasKey $hasKey;

    public function __construct(
        private string     $key,
        private Constraint $constraint)
    {
        $this->hasKey = new ArrayHasKey($key);
    }

    public function matches($other): bool
    {
        return $this->hasKey->evaluate($other, returnResult:true)
            && $this->constraint->evaluate($other[$this->key], returnResult:true);
    }

    public function toString(): string
    {
        return $this->hasKey->toString() . ' and its value ' . $this->constraint->toString();
    }
}
