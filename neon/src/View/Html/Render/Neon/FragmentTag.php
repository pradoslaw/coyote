<?php
namespace Neon\View\Html\Render\Neon;

use Neon\View\Html\Tag;

readonly class FragmentTag implements Tag
{
    public function __construct(private array $children)
    {
    }

    public function html(): string
    {
        $html = '';
        foreach ($this->children as $child) {
            if ($child === null) {
                continue;
            }
            /** @var Tag $child */
            $html .= $child->html();
        }
        return $html;
    }

    public function parentClass(): ?string
    {
        return null;
    }
}
