<?php
namespace Tests\Unit\OpenGraph;

use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture\Laravel;
use Tests\Unit\OpenGraph\Fixture\IsRelativeUri;
use Tests\Unit\OpenGraph\Fixture\ViewFixture;

class Test extends TestCase
{
    use Laravel\Application;

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

    private function ogProperty(string $property, string $uri): string
    {
        $view = new ViewFixture($this->htmlView($uri));
        return $view->metaProperty($property);
    }

    private function htmlView(string $uri): string
    {
        return $this->laravel->get($uri)->content();
    }
}
