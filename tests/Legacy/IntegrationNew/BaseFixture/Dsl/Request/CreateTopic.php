<?php
namespace Tests\Legacy\IntegrationNew\BaseFixture\Dsl\Request;

readonly class CreateTopic
{
    public function __construct(
        public string  $categorySlug,
        public string  $title,
        public ?string $discussMode,
    ) {}
}
