<?php
namespace Xenon;

readonly class Tag implements ViewItem
{
    public function __construct(
        public string $htmlTag,
        public array  $children,
    )
    {
    }
}
