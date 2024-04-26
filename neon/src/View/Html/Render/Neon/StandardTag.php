<?php
namespace Neon\View\Html\Render\Neon;

use Neon\View\Html\Tag;

readonly class StandardTag implements Tag
{
    private FragmentTag $children;
    private array $attributes;

    public function __construct(
        public ?string $parentClass,
        private string $tag,
        array          $attributes,
        array          $children,
    )
    {
        $this->children = new FragmentTag($children);
        $this->attributes = \array_diff_key($attributes, ['parentClass' => null]);
    }

    public function html(): string
    {
        return "<$this->tag{$this->attributesHtml()}>{$this->children->html()}</$this->tag>";
    }

    private function attributesHtml(): string
    {
        if (empty($this->attributes)) {
            return '';
        }
        return ' ' . \implode(' ', $this->htmlAttributes());
    }

    private function htmlAttributes(): array
    {
        return \array_map($this->htmlAttribute(...), \array_keys($this->attributes));
    }

    private function htmlAttribute(string $name): ?string
    {
        return $name . '="' . \htmlSpecialChars($this->attributes[$name]) . '"';
    }
}
