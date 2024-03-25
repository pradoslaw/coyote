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
        $this->selectors = $this->selectorOrText($selectors);
    }

    private function selectorOrText(array $selectors): array
    {
        if ($this->isAttribute($this->last($selectors))) {
            return $selectors;
        }
        return [...$selectors, 'text()'];
    }

    private function last(array $array): string
    {
        return \end($array);
    }

    private function isAttribute(string $selector): bool
    {
        return $selector[0] === '@';
    }

    public function xPath(): string
    {
        $selectors = \array_map($this->selector(...), $this->selectors);
        $leaf = \array_pop($selectors);
        return '//' . \implode('//', $selectors) . '/' . $leaf;
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
