<?php
namespace Neon\View\ViewModel;

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
