<?php
namespace Neon\View\Head;

readonly class Style implements Head
{
    public function __construct(private string $url)
    {
    }

    public function headHtml(callable $h): string
    {
        return $h('link', [], [
            'rel'  => 'stylesheet',
            'type' => 'text/css',
            'href' => $this->url,
        ]);
    }
}
