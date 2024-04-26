<?php
namespace Neon\View\Html\Render\Neon;

use Neon\View\Html\Tag;

readonly class StandardTag implements Tag
{
    private FragmentTag $children;

    public function __construct(
        public ?string $parentClass,
        private string $tag,
        private array  $attributes,
        array          $children,
    )
    {
        $this->children = new FragmentTag($children);
    }

    public function html(): string
    {
        return "<$this->tag{$this->htmlAttributes()}>{$this->children->html()}</$this->tag>";
    }

    private function htmlAttributes(): string
    {
        if (empty($this->attributes)) {
            return '';
        }
        return ' ' . \implode(' ',
                \array_map(
                    fn(string $key) => $key . '="' . \htmlSpecialChars($this->attributes[$key]) . '"',
                    \array_keys($this->attributes)));
    }
}
