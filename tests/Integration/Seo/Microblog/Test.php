<?php
namespace Tests\Integration\Seo\Microblog;

use PHPUnit\Framework\TestCase;
use Tests\Integration\BaseFixture;
use Tests\Integration\Seo;

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
        $id = $this->driver->newMicroblogReturnId();
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
        $id = $this->driver->newMicroblogReturnId();
        $this->assertIndexable("/Mikroblogi/View/$id");
    }
}
