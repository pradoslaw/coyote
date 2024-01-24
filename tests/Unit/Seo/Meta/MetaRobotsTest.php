<?php
namespace Tests\Unit\Seo\Meta;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Seo;

class MetaRobotsTest extends TestCase
{
    use Seo\Meta\Fixture\MetaProperty;

    /**
     * @test
     */
    public function homepage()
    {
        $this->assertSame('index,follow',
            $this->metaProperty('robots', uri:'/'));
    }

    /**
     * @test
     */
    public function userTags()
    {
        $this->assertSame('noindex,nofollow',
            $this->metaProperty('robots', uri:'/Forum/Interesting'));
    }
}
