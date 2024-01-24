<?php
namespace Tests\Unit\Seo\Meta;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Seo;

class MetaRobotsTest extends TestCase
{
    use Seo\Meta\Fixture\Assertion;

    /**
     * @test
     */
    public function homepage()
    {
        $this->assertIndexable('/');
    }

    /**
     * @test
     */
    public function userTags()
    {
        $this->assertNoIndexable('/Forum/Interesting');
    }

    /**
     * @test
     */
    public function category()
    {
        $this->assertIndexable('/Forum');
    }

    /**
     * @test
     */
    public function developerEnvironment()
    {
        $this->assertNoIndexable('http://4programmers.dev/');
    }
}
