<?php
namespace Neon\View\Html\Head;

use Neon\View\Html\Render;
use Neon\View\Html\Tag;

readonly class Style implements Head
{
    public function __construct(private string $url)
    {
    }

    public function render(Render $h): Tag
    {
        return $h->tag('link', [
            'rel'  => 'stylesheet',
            'type' => 'text/css',
            'href' => $this->url,
        ], []);
    }
}
