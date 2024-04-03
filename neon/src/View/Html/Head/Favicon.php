<?php
namespace Neon\View\Html\Head;

use Neon\View\Html\Render;

readonly class Favicon implements Head
{
    public function __construct(private string $faviconUrl)
    {
    }

    public function headHtml(Render $h): string
    {
        return $h->tag('link',
            [],
            ['rel' => 'shortcut icon', 'href' => $this->faviconUrl, 'type' => 'image/png']);
    }
}
