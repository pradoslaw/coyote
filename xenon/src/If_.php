<?php
namespace Xenon;

readonly class If_ implements ViewItem
{
    private FieldName $condition;
    private Fragment $body;
    private Fragment $else;

    public function __construct(string $conditionField, array $body, array $else)
    {
        $this->condition = new FieldName($conditionField);
        $this->body = new Fragment($body);
        $this->else = new Fragment($else);
    }

    public function ssrHtml(array $state): string
    {
        if ($this->condition->ssrValue($state)) {
            return $this->body->ssrHtml($state);
        }
        return $this->else->ssrHtml($state);
    }

    public function spaNode(): string
    {
        return "{$this->condition->spaVariable} ? {$this->body->spaExpression()} : {$this->else->spaExpression()}";
    }
}
