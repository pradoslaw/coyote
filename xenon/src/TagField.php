<?php
namespace Xenon;

readonly class TagField implements ViewItem
{
    public function __construct(
        private string $htmlTag,
        private string $fieldName,
    )
    {
    }

    public function ssrHtml(array $state): string
    {
        return "<$this->htmlTag>" . \htmlSpecialChars($state[$this->fieldName]) . "</$this->htmlTag>";
    }

    public function spaNode(): string
    {
        return "h('$this->htmlTag', {}, [store['$this->fieldName']])";
    }
}
