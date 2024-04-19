<?php
namespace Neon\View\Html\Render\Xenon;

use Neon\View\Html\Render\Tags;
use Neon\View\Html\Tag;
use Xenon\State;

readonly class XenonTags implements Tags
{
    public function __construct(private State $state)
    {
    }

    public function tag(?string $parentClass, string $tag, array $attributes, array $events, array $children): Tag
    {
        return new StandardTag($parentClass, $tag, $attributes, $events, $children);
    }

    public function many(array $children): Tag
    {
        return new FragmentTag($children);
    }

    public function html(string $html): Tag
    {
        return new HtmlTag($html);
    }

    public function text(string $text): Tag
    {
        return new TextTag($text);
    }

    public function if(string $conditionField, array $body, array $else): Tag
    {
        return new IfTag($conditionField, $body, $else);
    }

    public function setState(string $field, string $value): void
    {
        $this->state->setState($field, $value);
    }
}
