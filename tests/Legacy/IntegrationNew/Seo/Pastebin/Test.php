<?php
namespace Tests\Legacy\IntegrationNew\Seo\Pastebin;

use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\Seo;

class Test extends TestCase
{
    use Seo\Meta\Fixture\Assertion;

    public function test()
    {
        $this->assertNoIndexable('/Pastebin');
    }
}
