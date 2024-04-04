<?php
namespace Neon\View\Html\Head;

use Neon\View\Html\Render;
use Neon\View\Html\Tag;

readonly class Favicon implements Head
{
    public function __construct(private string $faviconUrl)
    {
    }

    public function render(Render $h): Tag
    {
        return $h->tag('link',
            ['rel' => 'shortcut icon', 'href' => $this->faviconUrl, 'type' => 'image/png'],
            []);
    }
}
