<?php
namespace Neon\View\Html\Render\Neon;

use Neon\View\Html\Render\Tags;

class NeonTags implements Tags
{
    public function tag(?string $parentClass, string $tag, array $attributes, array $children): NeonTag
    {
        return new StandardTag($parentClass, $tag, $attributes, $children);
    }

    public function many(array $children): NeonTag
    {
        return new FragmentTag($children);
    }

    public function html(string $html): NeonTag
    {
        return new HtmlTag($html);
    }

    public function text(string $text): NeonTag
    {
        return new HtmlTag(\htmlSpecialChars($text));
    }
}
