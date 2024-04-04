<?php
namespace Neon\View\Html;

readonly class Tag
{
    public function __construct(
        private string $html,
        public ?string $parentClass,
    )
    {
    }

    public function html(): string
    {
        return $this->html;
    }
}
