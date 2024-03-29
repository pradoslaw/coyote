<?php
namespace Neon\View\Html\Head;

readonly class Favicon implements Head
{
    public function __construct(private string $faviconUrl)
    {
    }

    public function headHtml(callable $h): string
    {
        return $h('link',
            [],
            ['rel' => 'shortcut icon', 'href' => $this->faviconUrl, 'type' => 'image/png']);
    }
}
