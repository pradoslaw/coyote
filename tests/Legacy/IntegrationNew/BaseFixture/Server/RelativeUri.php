<?php
namespace Tests\Legacy\IntegrationNew\BaseFixture\Server;

use Tests\Legacy\IntegrationNew\BaseFixture\Constraint\UrlPathEquals;
use Tests\Legacy\IntegrationNew\BaseFixture\Server;

trait RelativeUri
{
    use Server\Http;

    function relativeUri(string $relativeUri): UrlPathEquals
    {
        return new UrlPathEquals($this->server->baseUrl, $relativeUri);
    }
}
