<?php
namespace Neon\View\Head;

readonly class Title implements Head
{
    public function __construct(private string $title)
    {
    }

    public function headHtml(callable $h): string
    {
        return $h('title', [$this->title]);
    }
}
