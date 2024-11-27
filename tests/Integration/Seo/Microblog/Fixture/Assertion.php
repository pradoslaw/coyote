<?php
namespace Tests\Integration\Seo\Microblog\Fixture;

use PHPUnit\Framework\Assert;
use Tests\Integration\Seo;

trait Assertion
{
    use Seo\Meta\Fixture\MetaCanonical;

    function assertCanonicalNotPresent(string $uri): void
    {
        Assert::assertNull(
            $this->metaCanonical($uri),
            "Failed asserting that page '$uri' doesn't have <link rel=canonical> tag.");
    }
}
