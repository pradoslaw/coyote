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

    private function render(string $tag, array $children, string $className = null): string
    {
        $content = \implode('', $children);
        if ($className === null) {
            return "<$tag>$content</$tag>";
        }
        return "<$tag class=\"$className\">$content</$tag>";
    }
}
