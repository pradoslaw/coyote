<?php
namespace Tests\Unit\OpenGraph;

use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;

class Test extends TestCase
{
    use BaseFixture\Server\RelativeUri, Fixture\OpenGraph;

    /**
     * @test
     */
    public function type()
    {
        $this->assertThat(
            $this->metaProperty('og:type', uri:'/'),
            $this->identicalTo('website'));
    }

    /**
     * @test
     */
    public function url()
    {
        $this->assertThat(
            $this->metaProperty('og:url', uri:'/Forum'),
            $this->relativeUri('/Forum'));
    }

    /**
     * @test
     */
    public function locale()
    {
        $this->assertThat(
            $this->metaProperty('og:locale', uri:'/'),
            $this->identicalTo('pl_PL'));
    }

    /**
     * @test
     */
    public function title()
    {
        $this->assertThat(
            $this->metaProperty('og:title', uri:'/'),
            $this->identicalTo('Programowanie: serwis dla programistów'));
    }
}
