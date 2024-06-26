<?php
namespace Tests\Unit\Seo\Microblog;

use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;
use Tests\Unit\Seo;

class Test extends TestCase
{
    use BaseFixture\Forum\Models;
    use Seo\Microblog\Fixture\Assertion;
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
        $id = $this->models->newMicroblogReturnId();
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
        $id = $this->models->newMicroblogReturnId();
        $this->assertIndexable("/Mikroblogi/View/$id");
    }
}
