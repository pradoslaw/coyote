<?php
namespace Neon\View\Html\Render\Neon;

readonly class FragmentTag implements NeonTag
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
            /** @var NeonTag $child */
            $html .= $child->html();
        }
        return $html;
    }

    public function parentClass(): ?string
    {
        return null;
    }
}
