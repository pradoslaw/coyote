<?php
namespace Tests\Unit\OpenGraph\Fixture;

use Tests\Unit\BaseFixture\Server\Laravel;

trait OpenGraph
{
    use Laravel\Application;

    function ogProperty(string $property, string $uri): string
    {
        $view = new ViewFixture($this->htmlView($uri));
        return $view->metaProperty($property);
    }

    function htmlView(string $uri): string
    {
        return $this->laravel->get($uri)->assertSuccessful()->content();
    }
}
