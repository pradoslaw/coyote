<?php
namespace Neon\View\Html\Render\Neon;

use Neon\View\Html\Tag;

readonly class FragmentTag implements Tag
{
    public ?string $parentClass;

    public function __construct(private array $children)
    {
        $this->parentClass = $this->parentClass($this->children);
    }

    public function html(): string
    {
        $html = '';
        foreach ($this->children as $child) {
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

    private function parentClass(array $children): ?string
    {
        foreach ($children as $child) {
            if ($child instanceof Tag) {
                if ($child->parentClass) {
                    return $child->parentClass;
                }
            }
        }
        return null;
    }
}
