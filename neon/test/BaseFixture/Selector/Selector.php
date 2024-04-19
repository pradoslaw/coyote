<?php
namespace Neon\Test\BaseFixture\Selector;

readonly class Selector
{
    /** @var string[] */
    private array $selectors;

    public function __construct(string...$selectors)
    {
        foreach ($selectors as $selector) {
            if (\trim($selector) === '') {
                throw new \Exception('Failed to accept empty string selector.');
            }
        }
        $this->selectors = $selectors;
    }

    public function xPath(): string
    {
        $selectors = \array_map($this->selector(...), $this->selectors);
        if ($this->hasLeaf()) {
            $last = \array_pop($selectors);
            return '//' . \implode('//', $selectors) . '/' . $last;
        }
        return '//' . \implode('//', $selectors);
    }

    private function hasLeaf(): bool
    {
        $last = $this->lastSelector();
        return $last === 'text()' || $last[0] === '@';
    }

    private function lastSelector(): string
    {
        return $this->selectors[\array_key_last($this->selectors)];
    }

    private function selector(string $selector): string
    {
        if (\str_contains($selector, '.')) {
            return $this->xPathByClass($selector);
        }
        if ($selector[0] === '#') {
            return $this->xPathById($selector);
        }
        return $selector;
    }

    private function xPathByClass(string $selector): string
    {
        [$tag, $class] = \explode('.', $selector);
        $tag = $tag ?: '*';
        return "{$tag}[@class and contains(concat(' ', normalize-space(@class), ' '), ' $class ')]";
    }

    private function xPathById(string $selector): string
    {
        $id = \subStr($selector, 1);
        return "*[@id='$id']";
    }
}
