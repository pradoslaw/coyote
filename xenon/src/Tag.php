<?php
namespace Xenon;

readonly class Tag implements ViewItem
{
    private Fragment $children;

    /**
     * @param string $htmlTag
     * @param string[] $attributes
     * @param ViewItem[] $children
     */
    public function __construct(
        private string $htmlTag,
        private array  $attributes,
        array          $children)
    {
        $this->children = new Fragment($children);
    }

    public function ssrHtml(array $state): string
    {
        $innerHtml = $this->children->ssrHtml($state);
        return "<$this->htmlTag{$this->ssrAttributes()}>$innerHtml</$this->htmlTag>";
    }

    private function ssrAttributes(): string
    {
        if (empty($this->attributes)) {
            return '';
        }
        return ' ' . \implode(' ',
                \array_map(
                    fn(string $key) => $key . '="' . $this->attributes[$key] . '"',
                    \array_keys($this->attributes)));
    }

    public function spaNode(): string
    {
        $attributes = \json_encode($this->attributes);
        return "h('$this->htmlTag', $attributes, {$this->children->spaExpression()})";
    }
}
