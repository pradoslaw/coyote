<?php
namespace Neon\View;

class Render
{
    public function __invoke(string $tag, array $children, string|array $classNameOrAttributes = null): string
    {
        return $this->renderElement(
            $tag,
            $this->attributes($classNameOrAttributes),
            \implode('', $children));
    }

    private function attributes(string|array|null $classNameOrAttributes): array
    {
        if (\is_string($classNameOrAttributes)) {
            return ['class' => $classNameOrAttributes];
        }
        return $classNameOrAttributes ?? [];
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
}
