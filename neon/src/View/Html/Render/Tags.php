<?php
namespace Neon\View\Html\Render;

use Neon\View\Html\Tag;

interface Tags
{
    /**
     * @param string|null $parentClass
     * @param string $tag
     * @param string[] $attributes
     * @param Tag[]|string[] $children
     */
    public function tag(?string $parentClass, string $tag, array $attributes, array $events, array $children): Tag;

    public function many(array $children): Tag;

    public function html(string $html): Tag;

    public function text(string $text): Tag;

    public function if(string $conditionField, array $body, array $else): Tag;

    public function setState(string $field, string $value): void;
}
