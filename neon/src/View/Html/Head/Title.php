<?php
namespace Neon\View\Html\Head;

use Neon\View\Html\Render;

readonly class Title implements Head
{
    public function __construct(private string $title)
    {
    }

    public function headHtml(Render $h): string
    {
        return $h('title', [$this->title], []);
    }
}
