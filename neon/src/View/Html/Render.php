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
        return $this->tagWithElevatedClass($tag, $attributes, $this->childrenTags($children));
    }

    private function tagWithElevatedClass(string $tag, array $attributes, array $children): Tag
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

    private function childrenTags(array $children): array
    {
        return \array_map($this->childTag(...), \array_filter($children));
    }

    private function childTag(Tag|string $tagOrText): Tag
    {
        if (\is_string($tagOrText)) {
            return $this->tags->text($tagOrText);
        }
        return $tagOrText;
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
            if ($child->parentClass != null) {
                $classes[] = $child->parentClass;
            }
        }
        return $classes;
    }
}
