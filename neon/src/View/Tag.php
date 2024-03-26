<?php
namespace Neon\View;

readonly class Tag
{
    public function __construct(
        private string $html,
        public ?string $parentClass,
    )
    {
    }

    public function __toString(): string
    {
        return $this->html;
    }
}
