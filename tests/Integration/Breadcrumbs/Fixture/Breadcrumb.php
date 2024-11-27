<?php
namespace Tests\Integration\Breadcrumbs\Fixture;

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
