<?php
namespace Xenon;

readonly class If_ implements ViewItem
{
    public function __construct(
        private string $conditionField,
        private array  $body)
    {
    }

    public function ssrHtml(array $state): string
    {
        if ($state[$this->conditionField]) {
            return \implode('', \array_map(fn(ViewItem $item) => $item->ssrHtml([]), $this->body));
        }
        return '';
    }

    public function spaNode(): string
    {
        return "store.$this->conditionField ? {$this->spaConditionBody()} : ''";
    }

    private function spaConditionBody(): string
    {
        return \implode(',', \array_map(fn(ViewItem $item) => $item->spaNode(), $this->body));
    }
}
