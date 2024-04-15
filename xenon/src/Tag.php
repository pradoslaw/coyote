<?php
namespace Xenon;

readonly class Tag implements ViewItem
{
    /**
     * @param string $htmlTag
     * @param ViewItem[] $children
     */
    public function __construct(
        private string $htmlTag,
        private array  $children,
    )
    {
    }

    public function ssrHtml(array $state): string
    {
        $innerHtml = $this->ssrChildren($state);
        return "<$this->htmlTag>$innerHtml</$this->htmlTag>";
    }

    private function ssrChildren(array $state): string
    {
        return \implode('', \array_map(fn(ViewItem $item) => $item->ssrHtml($state), $this->children));
    }

    public function spaNode(): string
    {
        return "h('$this->htmlTag', {}, {$this->spaChildren()})";
    }

    private function spaChildren(): string
    {
        $vNodes = \implode(',', \array_map(fn(ViewItem $item) => $item->spaNode(), $this->children));
        return "[$vNodes]";
    }
}
