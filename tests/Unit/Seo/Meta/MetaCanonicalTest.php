<?php
namespace Tests\Unit\Seo\Meta;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Seo;

class MetaCanonicalTest extends TestCase
{
    use Seo\Meta\Fixture\MetaCanonical;

    /**
     * @test
     */
    public function canonical()
    {
        $this->assertSelfCanonical('/');
    }

    /**
     * @test
     */
    public function categories()
    {
        $this->assertSelfCanonical('/Forum');
    }

    /**
     * @test
     */
    public function https()
    {
        $this->assertSelfCanonicalAbsolute('https://4programmers.local/');
    }
}
