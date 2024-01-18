<?php
namespace Tests\Unit\Canonical;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Canonical;

class TrailingSlashTest extends TestCase
{
    use Canonical\Fixture\Assertion;

    /**
     * @test
     */
    public function canonical()
    {
        $this->assertCanonical($this->get('/Forum'));
    }

    /**
     * @test
     */
    public function trailingSlash()
    {
        $this->assertRedirect($this->get('/Forum/'), '/Forum', status:301);
    }

    /**
     * @test
     */
    public function postCanonical()
    {
        $this->assertCanonical($this->post('/Forum/Preview'));
    }

    /**
     * @test
     */
    public function postTrailingSlash()
    {
        $this->assertRedirect(
            $this->post('/Forum/Preview/'),
            '/Forum/Preview',
            status:308);
    }

    /**
     * @test
     */
    public function headCanonical()
    {
        $this->assertCanonical($this->head('/Forum'));
    }

    /**
     * @test
     */
    public function headTrailingSlash()
    {
        $this->assertRedirect($this->head('/Forum/'), '/Forum', status:308);
    }
}
