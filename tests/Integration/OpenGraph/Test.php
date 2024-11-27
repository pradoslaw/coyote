<?php
namespace Tests\Integration\OpenGraph;

use PHPUnit\Framework\TestCase;
use Tests\Integration\BaseFixture;

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
    public function urlCanonical()
    {
        $this->assertThat(
            $this->metaProperty('og:url', uri:'/Forum?sort=id&page=2'),
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
