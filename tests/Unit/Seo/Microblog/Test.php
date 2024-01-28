<?php
namespace Tests\Unit\Seo\Microblog;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Seo;

class Test extends TestCase
{
    use Seo\Microblog\Fixture\Assertion;

    public function test()
    {
        $this->assertCanonicalNotPresent('/Mikroblogi');
    }
}
