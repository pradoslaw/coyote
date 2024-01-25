<?php
namespace Tests\Unit\Seo\Meta;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Seo;

class MetaCanonicalTest extends TestCase
{
    use Seo\Meta\Fixture\MetaCanonical;
    use Seo\Meta\Fixture\Models;

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
    public function categoriesNoPage()
    {
        $this->assertCanonical('/Forum?page=2', '/Forum');
    }

    /**
     * @test
     */
    public function categoryPage()
    {
        $this->newCategory('Fruits');
        $this->assertSelfCanonical('/Forum/Fruits?page=2');
    }

    /**
     * @test
     */
    public function topicPage()
    {
        [$category, $topic] = $this->newTopic();
        $this->assertSelfCanonical("/Forum/$category/$topic?page=2");
    }

    /**
     * @test
     */
    public function ignoreQueryParam()
    {
        $this->assertCanonical('/Praca?query=param', '/Praca');
    }

    /**
     * @test
     */
    public function https()
    {
        $this->assertSelfCanonicalAbsolute('https://4programmers.local/');
    }

    /**
     * @test
     */
    public function http()
    {
        $canonical = $this->metaCanonical('http://4programmers.local/');
        $this->assertSame('https://4programmers.local/', $canonical);
    }
}
