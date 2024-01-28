<?php
namespace Tests\Unit\Seo\Microblog;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Seo;

class Test extends TestCase
{
    use Seo\Microblog\Fixture\Assertion;
    use Seo\Microblog\Fixture\Models;
    use Seo\Meta\Fixture\MetaCanonical;
    use Seo\Meta\Fixture\Assertion;

    /**
     * @test
     */
    public function paginationNoCanonical()
    {
        $this->assertCanonicalNotPresent('/Mikroblogi');
    }

    /**
     * @test
     */
    public function microblogSelfCanonical()
    {
        $id = $this->newMicroblog();
        $this->assertSelfCanonical("/Mikroblogi/View/$id");
    }

    /**
     * @test
     */
    public function pagination()
    {
        $this->assertCrawlable('/Mikroblogi');
    }

    /**
     * @test
     */
    public function microblogIndexable()
    {
        $id = $this->newMicroblog();
        $this->assertIndexable("/Mikroblogi/View/$id");
    }
}
