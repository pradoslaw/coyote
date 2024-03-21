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
        $this->selectors = [...$selectors, 'text()'];
    }

    public function xPath(): string
    {
        $selectors = \array_map($this->selector(...), $this->selectors);
        return '//' . \implode('/', $selectors);
    }

    private function selector(string $selector): string
    {
        if (\strPos($selector, '.') === false) {
            return $selector;
        }
        [$tag, $class] = \explode('.', $selector);
        $tag = $tag ?: '*';
        return "{$tag}[@class and contains(concat(' ', normalize-space(@class), ' '), ' $class ')]";
    }
}
