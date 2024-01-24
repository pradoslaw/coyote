<?php
namespace Tests\Unit\Seo\Meta;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Seo;

class Test extends TestCase
{
    use Seo\Meta\Fixture\MetaProperty;

    /**
     * @test
     */
    public function robots()
    {
        $this->assertSame(
            'index, follow',
            $this->metaProperty('robots', uri:'/'));
    }
}
