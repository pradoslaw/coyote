<?php
namespace Neon\View\Html\Head;

use Neon\View\Html\Render;

readonly class Script implements Head
{
    public function __construct(private string $url)
    {
    }

    public function headHtml(Render $h): string
    {
        return $h->tag('script', ['src' => $this->url], []);
    }
}
