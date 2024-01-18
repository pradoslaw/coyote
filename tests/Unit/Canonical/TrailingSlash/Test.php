<?php
namespace Tests\Unit\Canonical\TrailingSlash;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Canonical;

class Test extends TestCase
{
    use Canonical\Fixture\Assertion;

    /**
     * @test
     */
    public function forumCategories()
    {
        $this->assertRedirect($this->get('/Forum/Categories/'), '/Forum/Categories', status:301);
    }

    /**
     * @test
     */
    public function job()
    {
        $this->assertRedirect($this->get('/Praca/'), '/Praca', status:301);
    }

    /**
     * @test
     */
    public function microblog()
    {
        $this->assertRedirect($this->get('/Mikroblogi/'), '/Mikroblogi', status:301);
    }

    /**
     * @test
     */
    public function homepage()
    {
        $this->assertCanonical($this->get('/'));
    }
}
