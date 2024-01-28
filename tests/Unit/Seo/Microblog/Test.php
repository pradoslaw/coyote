<?php
namespace Tests\Unit\Seo\Microblog;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Seo;

class Test extends TestCase
{
    use Seo\Microblog\Fixture\Assertion;
    use Seo\Microblog\Fixture\Models;
    use Seo\Meta\Fixture\MetaCanonical;

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
}
