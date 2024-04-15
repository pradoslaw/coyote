<?php
namespace Xenon;

readonly class Text implements ViewItem
{
    public function __construct(private string $text)
    {
    }

    public function ssrHtml(array $state): string
    {
        return \htmlSpecialChars($this->text);
    }

    public function spaNode(): string
    {
        return \json_encode($this->text);
    }
}
