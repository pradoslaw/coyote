<?php
namespace Neon\Domain;

class Tag
{
    public function __construct(
        public string  $name,
        public ?string $imageUrl,
    )
    {
    }
}
