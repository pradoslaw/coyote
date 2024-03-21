<?php
namespace Neon;

use Neon\View\Page;

class View
{
    private Page $page;

    public function __construct(array $head, array $body)
    {
        $this->page = new Page($head, $body);
    }

    public function html(): string
    {
        return $this->page->html($this->render(...));
    }

    private function render(string $tag, array $children, string|array $classNameOrAttributes = null): string
    {
        return $this->renderElement(
            $tag,
            $this->attributes($classNameOrAttributes),
            \implode('', $children));
    }

    private function attributes(string|array $classNameOrAttributes = null): array
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
