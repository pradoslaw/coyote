<?php
namespace Tests\Unit\Seo\Schema\Fixture;

use Tests\Unit\BaseFixture\Server;
use Tests\Unit\BaseFixture\View\ViewFixture;

trait Schema
{
    use Server\Http;

    function schema(string $uri, string $type): ?array
    {
        return $this->firstSchema(new ViewFixture($this->viewHtml($uri)), $type);
    }

    function viewHtml(string $uri): string
    {
        return $this->server->get($uri)->assertSuccessful()->content();
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
