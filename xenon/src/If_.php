<?php
namespace Xenon;

readonly class If_ implements ViewItem
{
    private FieldName $condition;
    private Fragment $conditionBody;

    public function __construct(string $conditionField, array $body)
    {
        $this->condition = new FieldName($conditionField);
        $this->conditionBody = new Fragment($body);
    }

    public function ssrHtml(array $state): string
    {
        if ($this->condition->ssrValue($state)) {
            return $this->conditionBody->ssrHtml($state);
        }
        return '';
    }

    public function spaNode(): string
    {
        return "{$this->condition->spaVariable} ? {$this->conditionBody->spaExpression()} : []";
    }
}
