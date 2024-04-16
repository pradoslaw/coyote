<?php
namespace Xenon;

readonly class Fragment
{
    /**
     * @param ViewItem[] $items
     */
    public function __construct(private array $items)
    {
    }

    public function ssrHtml(array $state): string
    {
        return \implode('', \array_map(
            fn(ViewItem $item) => $item->ssrHtml($state),
            $this->items));
    }

    public function spaExpression(): string
    {
        $vNodes = \implode(',', \array_map(
            fn(ViewItem $item) => $item->spaNode(),
            $this->items));
        return "[$vNodes]";
    }
}
