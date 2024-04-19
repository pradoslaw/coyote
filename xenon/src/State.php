<?php
namespace Xenon;

class State
{
    public function __construct(private array $state)
    {
    }

    public function setState(string $field, string $value): void
    {
        $this->state[$field] = $value;
    }

    public function toArray(): array
    {
        return $this->state;
    }
}
