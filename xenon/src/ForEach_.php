<?php
namespace Xenon;

readonly class ForEach_ implements ViewItem
{
    private FieldName $list;
    private Fragment $listItem;

    /**
     * @param string $listField
     * @param ViewItem[] $listItem
     */
    public function __construct(string $listField, array $listItem)
    {
        $this->list = new FieldName($listField);
        $this->listItem = new Fragment($listItem);
    }

    public function ssrHtml(array $state): string
    {
        $html = '';
        foreach ($state[$this->list->name] as $index => $item) {
            $html .= $this->listItem->ssrHtml([
                ...$state,
                '$index' => $index,
                '$item'  => $item,
            ]);
        }
        return $html;
    }

    public function spaNode(): string
    {
        return "{$this->list->spaVariable}.map((\$item, \$index) => {$this->listItem->spaExpression()})";
    }
}
