<?php
namespace Tests\Integration\BaseFixture\Server;

use Tests\Integration\BaseFixture\Constraint\UrlPathEquals;
use Tests\Integration\BaseFixture\Server;

trait RelativeUri
{
    use Server\Http;

    function relativeUri(string $relativeUri): UrlPathEquals
    {
        return new UrlPathEquals($this->server->baseUrl, $relativeUri);
    }
}
