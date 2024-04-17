<?php
namespace Neon\View\Html;

use Neon\View\Html\Render\Tags;

readonly class Render
{
    public function __construct(private Tags $tags)
    {
    }

    public function tag(string $tag, array $attributes, array $children): Tag
    {
        $elevatedClass = $this->elevatedClass($children);
        if (!empty($elevatedClass)) {
            $attributes['class'] = \trim(($attributes['class'] ?? '') . ' ' . $elevatedClass);
        }
        return $this->tags->tag(
            $attributes['parentClass'] ?? null,
            $tag,
            $attributes,
            $children,
        );
    }

    public function many(array $children): Tag
    {
        return $this->tags->many($children);
    }

    public function html(string $html): Tag
    {
        return $this->tags->html($html);
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
