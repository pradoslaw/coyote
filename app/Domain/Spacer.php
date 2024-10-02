<?php
namespace Coyote\Domain;

readonly class Spacer
{
    public function __construct(private int $spaces)
    {
        if ($spaces < 1) {
            throw new \InvalidArgumentException();
        }
    }

    public function fitInSpace(array $items): array
    {
        return $this->partitionArrayAt($items, $this->outputLength($items));
    }

    private function partitionArrayAt(array $items, int $length): array
    {
        return [
            \array_slice($items, 0, $length),
            \count($items) - $length,
        ];
    }

    private function outputLength(array $items): int
    {
        return $this->spaces - $this->padding($items);
    }

    private function padding(array $items): int
    {
        if (\count($items) === $this->spaces) {
            return 0;
        }
        return 1;
    }
}
