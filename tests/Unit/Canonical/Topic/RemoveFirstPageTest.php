<?php
namespace Tests\Unit\Canonical\Topic;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Canonical;

class RemoveFirstPageTest extends TestCase
{
    use Canonical\Fixture\Assertion,
        Canonical\Topic\Fixture\Models;

    /**
     * @test
     */
    public function base()
    {
        $uri = $this->newTopic();
        $this->assertNoRedirectGet("/Forum/{$uri}");
    }

    /**
     * @test
     */
    public function pageFirst()
    {
        $uri = $this->newTopic();
        $this->assertRedirectGet("/Forum/{$uri}?page=1", "/Forum/{$uri}");
    }

    /**
     * @test
     */
    public function queryParamPreserve()
    {
        $uri = $this->newTopic();
        $this->assertRedirectGet(
            "/Forum/{$uri}?order=asc&page=1",
            "/Forum/{$uri}?order=asc");
    }

    /**
     * @test
     */
    public function pageSecond()
    {
        $uri = $this->newTopic();
        $this->assertNoRedirectGet("/Forum/{$uri}?page=2");
    }
}
