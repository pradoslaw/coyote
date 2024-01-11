<?php
namespace Tests\Unit\OpenGraph;

use PHPUnit\Framework\TestCase;
use Tests\Unit\OpenGraph\Fixture\IsRelativeUri;

class Test extends TestCase
{
    use Fixture\OpenGraph;

    /**
     * @test
     */
    public function type()
    {
        $this->assertThat(
            $this->ogProperty('og:type', uri:'/'),
            $this->identicalTo('website'));
    }

    /**
     * @test
     */
    public function url()
    {
        $this->assertThat(
            $this->ogProperty('og:url', uri:'/Forum'),
            new IsRelativeUri('/Forum', $this->laravel));
    }

    /**
     * @test
     */
    public function locale()
    {
        $this->assertThat(
            $this->ogProperty('og:locale', uri:'/'),
            $this->identicalTo('pl_PL'));
    }
}
