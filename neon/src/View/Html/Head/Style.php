<?php
namespace Neon\View\Html\Head;

use Neon\View\Html\Render;

readonly class Style implements Head
{
    public function __construct(private string $url)
    {
    }

    public function headHtml(Render $h): string
    {
        return $h->tag('link', [], [
            'rel'  => 'stylesheet',
            'type' => 'text/css',
            'href' => $this->url,
        ]);
    }
}
