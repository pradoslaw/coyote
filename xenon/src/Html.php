<?php
namespace Xenon;

readonly class Html implements ViewItem
{
    public function __construct(
        private string $tag,
        private string $innerHtml,
    )
    {
    }

    public function ssrHtml(array $state): string
    {
        return "<$this->tag>$this->innerHtml</$this->tag>";
    }

    public function spaNode(): string
    {
        $html = \json_encode($this->innerHtml);
        return "h('$this->tag', {innerHTML:$html}, [])";
    }
}
