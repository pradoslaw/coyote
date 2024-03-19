<?php
namespace Neon;

use Neon\View\Page;

class View
{
    private Page $page;

    public function __construct(
        string $applicationName,
        string $sectionTitle,
        array  $events)
    {
        $this->page = new Page(
            $applicationName,
            $sectionTitle,
            $events);
    }

    public function html(): string
    {
        return $this->page->html($this->render(...));
    }

    private function render(string $tag, array $children): string
    {
        $content = \implode('', $children);
        return "<$tag>$content</$tag>";
    }
}
