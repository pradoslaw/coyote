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
        foreach ($state[$this->listField] as $index => $item) {
            $html .= $this->listItem->ssrHtml([
                '$index' => $index,
                '$item'  => $item,
                ...$state,
            ]);
        }
        return $html;
    }

    public function spaNode(): string
    {
        return "{$this->spaListVariable()}.map((\$item, \$index) => {$this->listItem->spaExpression()})";
    }

    private function spaListVariable(): string
    {
        if ($this->listField[0] === '$') {
            return $this->listField;
        }
        return "store.$this->listField";
    }
}
