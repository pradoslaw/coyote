<?php
namespace Tests\Unit\Seo\Fixture;

use Tests\Unit\BaseFixture\Server\Laravel;
use Tests\Unit\BaseFixture\ViewFixture;

trait Schema
{
    use Laravel\Application;

    function schema(string $uri, string $type): ?array
    {
        return $this->firstSchema(new ViewFixture($this->viewHtml($uri)), $type);
    }

    function viewHtml(string $uri): string
    {
        return $this->laravel->get($uri)->assertSuccessful()->content();
    }

    function firstSchema(ViewFixture $viewFixture, string $type): ?array
    {
        foreach ($this->schemaObjects($viewFixture) as $schema) {
            if ($schema['@type'] === $type) {
                return $schema;
            }
        }
        return null;
    }

    function schemaObjects(ViewFixture $viewFixture): iterable
    {
        foreach ($viewFixture->javaScriptDeclarations() as $declaration) {
            if ($declaration->type() === 'application/ld+json') {
                yield $declaration->object();
            }
        }
    }
}
