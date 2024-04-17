<?php
namespace Neon\View\Html\Render\Neon;

use Neon\View\Html\Render\Tags;
use Neon\View\Html\Tag;

class NeonTags implements Tags
{
    public function tag(?string $parentClass, string $tag, array $attributes, array $children): Tag
    {
        return new StandardTag($parentClass, $tag, $attributes, $children);
    }

    public function many(array $children): Tag
    {
        return new FragmentTag($children);
    }

    public function html(string $html): Tag
    {
        return new HtmlTag($html);
    }
}
