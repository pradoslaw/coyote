<?php
namespace Neon;

use Neon\View\Page;
use Neon\View\Section;

class View
{
    private Page $page;

    public function __construct(string $title, Section $section)
    {
        $this->page = new Page($title, $section);
    }

    public function html(): string
    {
        return $this->page->html($this->render(...));
    }

    private function render(string $tag, array $children, string $className = null): string
    {
        $content = \implode('', $children);
        if ($className === null) {
            return "<$tag>$content</$tag>";
        }
        return "<$tag class=\"$className\">$content</$tag>";
    }
}
