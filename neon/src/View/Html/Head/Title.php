<?php
namespace Neon\View\Html\Head;

use Neon\View\Html\Render;
use Neon\View\Html\Tag;

readonly class Title implements Head
{
    public function __construct(private string $title)
    {
    }

    public function render(Render $h): Tag
    {
        return $h->tag('title', [], [$this->title]);
    }
}
