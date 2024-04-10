<?php
namespace Neon\View;

use Neon\View\Html\Page;
use Neon\View\Html\Render;

class HtmlView
{
    private Page $page;

    public function __construct(array $head, array $body)
    {
        $this->page = new Page($head, $body);
    }

    public function html(): string
    {
        $h = new Render();
        $pageTag = $h->many($this->page->render($h));
        return $pageTag->html();
    }
}
