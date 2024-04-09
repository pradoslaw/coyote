<?php
namespace Neon\View\Components\Navigation;

readonly class Link
{
    public function __construct(
        public string $title,
        public string $href,
        public bool   $bold,
    )
    {
    }
}
