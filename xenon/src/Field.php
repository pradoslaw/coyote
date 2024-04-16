<?php
namespace Xenon;

readonly class Field implements ViewItem
{
    public function __construct(private string $fieldName)
    {
    }

    public function ssrHtml(array $state): string
    {
        return \htmlSpecialChars($state[$this->fieldName]);
    }

    public function spaNode(): string
    {
        return "store.$this->fieldName";
    }
}
