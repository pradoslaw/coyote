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
}
