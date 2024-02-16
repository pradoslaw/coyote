<?php
namespace Tests\Unit\BaseFixture\Server;

use Tests\Unit\BaseFixture\Constraint\UrlPathEquals;
use Tests\Unit\BaseFixture\Server;

trait RelativeUri
{
    use Server\Http;

    function relativeUri(string $relativeUri): UrlPathEquals
    {
        return new UrlPathEquals($this->server->baseUrl, $relativeUri);
    }
}
