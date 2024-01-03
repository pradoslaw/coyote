<?php
namespace Tests\Unit\OpenGraph;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture\Laravel;

class Test extends TestCase
{
    use Laravel\Application;

    public function test()
    {
        $view = $this->view('/');
        Assert::assertSame(
            'website',
            $view->metaProperty('og:type'),
        );
    }

    private function view(string $uri): ViewFixture
    {
        return new ViewFixture($this->htmlView($uri));
    }

    private function htmlView(string $uri): string
    {
        return $this->laravel->get($uri)->content();
    }
}
