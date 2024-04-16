<?php
namespace Neon\View\Html;

use Neon\View\Html\Render\Neon\FragmentTag;
use Neon\View\Html\Render\Neon\HtmlTag;
use Neon\View\Html\Render\Neon\StandardTag;

class Render
{
    public function tag(string $tag, array $attributes, array $children): Tag
    {
        $elevatedClass = $this->elevatedClass($children);
        if (!empty($elevatedClass)) {
            $attributes['class'] = \trim(($attributes['class'] ?? '') . ' ' . $elevatedClass);
        }
        return new StandardTag(
            $attributes['parentClass'] ?? null,
            $tag,
            $attributes,
            $children,
        );
    }

    public function many(array $children): Tag
    {
        return new FragmentTag($children);
    }

    public function html(string $html): Tag
    {
        return new HtmlTag($html);
    }

    private function elevatedClass(array $children): string
    {
        return \implode(' ',
            \array_unique(
                $this->elevatedClasses($children)));
    }

    private function elevatedClasses(array $children): array
    {
        $classes = [];
        foreach ($children as $child) {
            if ($child instanceof Tag) {
                if ($child->parentClass != null) {
                    $classes[] = $child->parentClass;
                }
            }
        }
        return $classes;
    }
}
