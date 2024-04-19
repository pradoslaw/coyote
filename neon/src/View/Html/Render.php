<?php
namespace Neon\View\Html;

use Neon\View\Html\Render\Tags;

readonly class Render
{
    public function __construct(private Tags $tags)
    {
    }

    public function tag(string $tag, array $attributes, array $children, array $events = []): Tag
    {
        return $this->tagWithElevatedClass($tag, $attributes, $events, $this->childrenTags($children));
    }

    private function tagWithElevatedClass(string $tag, array $attributes, array $events, array $children): Tag
    {
        $elevatedClass = $this->elevatedClass($children);
        if (!empty($elevatedClass)) {
            $attributes['class'] = \trim(($attributes['class'] ?? '') . ' ' . $elevatedClass);
        }
        return $this->tags->tag(
            $attributes['parentClass'] ?? null,
            $tag,
            $attributes,
            $events,
            $children,
        );
    }

    private function childrenTags(array $children): array
    {
        return \array_map($this->childTag(...), \array_filter($children));
    }

    public function createField(string $field, string $initialValue): void
    {
        $this->tags->setState($field, $initialValue);
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

    public function if(string $conditionField, array $body, array $else = []): Tag
    {
        return $this->tags->if($conditionField, 
            $this->childrenTags($body), 
            $this->childrenTags($else));
    }

    public function html(string $html): Tag
    {
        return $this->tags->html($html);
    }

    /**
     * @param Tag[] $children
     */
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
            $class = $child->parentClass();
            if ($class != null) {
                $classes[] = $class;
            }
        }
        return $classes;
    }
}
