<?php
namespace Tests\Unit\Seo\Pastebin;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Seo;

class Test extends TestCase
{
    use Seo\Meta\Fixture\Assertion;

    public function test()
    {
        $this->assertNoIndexable('/Pastebin');
    }
}
