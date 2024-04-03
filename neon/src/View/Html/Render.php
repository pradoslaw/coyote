<?php
namespace Neon\View\Html;

class Render
{
    public function tag(string $tag, string|array $classNameOrAttributes, array $children): Tag
    {
        $attributes = $this->attributes($classNameOrAttributes);
        $childClasses = $this->elevatedClass($children);
        if (!empty($childClasses)) {
            $attributes['class'] = \trim(($attributes['class'] ?? '') . ' ' . $childClasses);
        }
        return new Tag(
            $this->renderElement(
                $tag,
                $attributes,
                \implode('', $children)),
            $attributes['parentClass'] ?? null,
        );
    }

    private function attributes(string|array $classNameOrAttributes): array
    {
        if (\is_string($classNameOrAttributes)) {
            return ['class' => $classNameOrAttributes];
        }
        return $classNameOrAttributes;
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
}
