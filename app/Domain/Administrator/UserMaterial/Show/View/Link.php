<?php
namespace Coyote\Domain\Administrator\UserMaterial\Show\View;

readonly class Link
{
    public function __construct(
        public string $href,
        public string $label,
    )
    {
    }
}
