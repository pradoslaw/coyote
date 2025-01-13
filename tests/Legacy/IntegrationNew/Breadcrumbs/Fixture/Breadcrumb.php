<?php
namespace Tests\Legacy\IntegrationNew\Breadcrumbs\Fixture;

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
