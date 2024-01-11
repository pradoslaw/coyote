<?php
namespace Tests\Unit\BaseFixture;

use Tests\Unit\BaseFixture\Constraint\IsRelativeUri;

trait RelativeUri
{
    use Laravel\Application;

    function relativeUri(string $relativeUri): IsRelativeUri
    {
        return new IsRelativeUri($relativeUri, $this->laravel);
    }
}
