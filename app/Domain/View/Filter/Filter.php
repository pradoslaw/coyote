<?php
namespace Coyote\Domain\View\Filter;

readonly class Filter
{
    public function __construct(private string $filter)
    {
    }

    public function toArray(): array
    {
        $array = [];
        foreach (\explode(' ', $this->filter) as $format) {
            $this->integer($array, $format, 'reporter');
            $this->integer($array, $format, 'author');
            $this->boolean($array, $format, 'reported');
            $this->boolean($array, $format, 'deleted');
            $this->choice($array, $format, 'type', ['post', 'comment', 'microblog']);
        }
        return $array;
    }

    private function integer(array &$array, string $format, string $key): void
    {
        $parts = \explode(':', $format);
        if (count($parts) === 1) {
            return;
        }
        [$k, $value] = $parts;
        if ($k === $key) {
            if (\cType_digit($value)) {
                $array[$key] = (int)$value;
            }
        }
    }

    private function boolean(array &$array, string $format, string $key): void
    {
        if ($format === 'is:' . $key) {
            $array[$key] = true;
        }
        if ($format === 'not:' . $key) {
            $array[$key] = false;
        }
    }

    private function choice(array &$array, string $format, string $key, array $values): void
    {
        foreach ($values as $value) {
            if ($format === "$key:$value") {
                $array[$key] = $value;
            }
        }
    }
}
