<?php
namespace Neon\View\Html;

class Render
{
    public function tag(string $tag, array $attributes, array $children): Tag
    {
        $elevatedClass = $this->elevatedClass($children);
        if (!empty($elevatedClass)) {
            $attributes['class'] = \trim(($attributes['class'] ?? '') . ' ' . $elevatedClass);
        }
        return new Tag(
            $this->renderElement(
                $tag,
                $attributes,
                $this->childrenToString($children)),
            $attributes['parentClass'] ?? null,
        );
    }

    public function many(array $children): Tag
    {
        return new Tag($this->childrenToString($children), null);
    }

    public function html(string $string): Tag
    {
        return new Tag($string, null);
    }

    private function renderElement(string $tag, array $attributes, string $innerHtml): string
    {
        if (empty($attributes)) {
            return "<$tag>$innerHtml</$tag>";
        }
        $attributesHtml = $this->formatAttributes($attributes);
        return "<$tag $attributesHtml>$innerHtml</$tag>";
    }

    private function formatAttributes(array $attributes): string
    {
        return \implode(' ',
            \array_map(
                fn(string $key) => $key . '="' . $attributes[$key] . '"',
                \array_keys($attributes)));
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

    private function childrenToString(array $children): string
    {
        $html = '';
        foreach ($children as $child) {
            if ($child === null) {
                continue;
            }
            if (\is_string($child)) {
                $html .= \htmlSpecialChars($child);
            } else {
                /** @var Tag $child */
                $html .= $child->html();
            }
        }
        return $html;
    }
}
