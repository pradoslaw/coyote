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
        if ($this->fieldName[0] === '$') {
            return $this->fieldName;
        }
        return "store.$this->fieldName";
    }
}
