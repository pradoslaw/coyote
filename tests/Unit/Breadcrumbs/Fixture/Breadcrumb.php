<?php
namespace Tests\Unit\Breadcrumbs\Fixture;

readonly class Breadcrumb
{
    public function __construct(
        public string  $name,
        public bool    $clickable,
        public ?string $href = null,
    )
    {
    }
}
