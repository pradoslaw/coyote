<?php
namespace Neon;

use Neon\View\Page;
use Neon\View\Render;

class View
{
    private Page $page;

    public function __construct(array $head, array $body)
    {
        $this->page = new Page($head, $body);
    }

    public function html(): string
    {
        return $this->page->html(new Render());
    }
}
