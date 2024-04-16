<?php
namespace Xenon;

readonly class Tag implements ViewItem
{
    private Fragment $children;

    /**
     * @param string $htmlTag
     * @param ViewItem[] $children
     */
    public function __construct(private string $htmlTag, array $children)
    {
        $this->children = new Fragment($children);
    }

    public function ssrHtml(array $state): string
    {
        $innerHtml = $this->children->ssrHtml($state);
        return "<$this->htmlTag>$innerHtml</$this->htmlTag>";
    }

    public function spaNode(): string
    {
        return "h('$this->htmlTag', {}, {$this->children->spaExpression()})";
    }
}
