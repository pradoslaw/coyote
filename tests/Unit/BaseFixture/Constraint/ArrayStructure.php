<?php
namespace Tests\Unit\BaseFixture\Constraint;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Constraint;

class ArrayStructure extends Constraint
{
    /** @var (Constraint|string)[] */
    private array $structure;
    /** @var string[] */
    private array $failedKeys;

    public function __construct(array $structure)
    {
        $this->structure = \array_map([$this, 'constraint'], $structure);
    }

    public function constraint(mixed $constraint): Constraint
    {
        if ($constraint instanceof Constraint) {
            return $constraint;
        }
        return Assert::identicalTo($constraint);
    }

    protected function matches($other): bool
    {
        $this->failedKeys = $this->failedKeys($other);
        return empty($this->failedKeys);
    }

    private function failedKeys(array $other): array
    {
        $keys = [];
        foreach ($this->structure as $key => $constraint) {
            if (!$this->arrayItemMatches($other, $key)) {
                $keys[] = $key;
            }
        }
        return $keys;
    }

    private function arrayItemMatches(array $array, int|string $key): bool
    {
        if (\array_key_exists($key, $array)) {
            return $this->structure[$key]->evaluate($array[$key], '', true);
        }
        return false;
    }

    protected function failureDescription($other): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return \implode(' and ', $this->constraintMessages());
    }

    private function constraintMessages(): array
    {
        $messages = [];
        foreach ($this->structure as $key => $constraint) {
            if (\in_array($key, $this->failedKeys)) {
                $messages[] = "value at {$this->exporter()->export($key)} {$constraint->toString()}";
            }
        }
        return $messages;
    }
}
