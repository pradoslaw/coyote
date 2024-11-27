<?php
namespace Tests\Integration\Canonical\Topic;

use PHPUnit\Framework\TestCase;
use Tests\Integration\Canonical;

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
    public function queryParamPreserveOrder()
    {
        $uri = $this->newTopic();
        $this->assertRedirectGet(
            "/Forum/{$uri}?bbb=222&page=1&aaa=111",
            "/Forum/{$uri}?bbb=222&aaa=111");
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
