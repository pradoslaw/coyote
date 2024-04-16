<?php
namespace Xenon;

readonly class ForEach_ implements ViewItem
{
    private Fragment $listItem;

    /**
     * @param string $listField
     * @param ViewItem[] $listItem
     */
    public function __construct(private string $listField, array $listItem)
    {
        $this->listItem = new Fragment($listItem);
    }

    public function ssrHtml(array $state): string
    {
        $html = '';
        foreach ($state[$this->listField] as $_) {
            $html .= $this->listItem->ssrHtml($state);
        }
        return $html;
    }

    public function spaNode(): string
    {
        return "store.$this->listField.map(() => {$this->listItem->spaExpression()})";
    }
}
