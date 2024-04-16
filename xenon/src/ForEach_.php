<?php
namespace Xenon;

readonly class ForEach_ implements ViewItem
{
    /**
     * @param string $listField
     * @param ViewItem[] $listItem
     */
    public function __construct(
        private string $listField,
        private array  $listItem,
    )
    {
    }

    public function ssrHtml(array $state): string
    {
        $html = '';
        foreach ($state[$this->listField] as $_) {
            $html .= $this->ssrListItem();
        }
        return $html;
    }

    private function ssrListItem(): string
    {
        return \implode('', \array_map(
            fn(ViewItem $item) => $item->ssrHtml([]),
            $this->listItem));
    }

    public function spaNode(): string
    {
        return "store.$this->listField.map(() => {$this->spaListItem()})";
    }

    private function spaListItem(): string
    {
        $vNodes = \implode(',', \array_map(
            fn(ViewItem $item) => $item->spaNode(),
            $this->listItem));
        return "[$vNodes]";
    }
}
