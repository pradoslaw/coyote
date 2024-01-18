<?php
namespace Tests\Unit\BaseFixture\Server;

use Tests\Unit\BaseFixture\Constraint\IsRelativeUri;
use Tests\Unit\BaseFixture\Server;

trait RelativeUri
{
    use Server\Http;

    function relativeUri(string $relativeUri): IsRelativeUri
    {
        return new IsRelativeUri($relativeUri, $this->server->baseUrl);
    }
}
