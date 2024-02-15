<?php
namespace Tests\Unit\Breadcrumbs;

use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture\Server;
use Tests\Unit\Breadcrumbs;
use Tests\Unit\Breadcrumbs\Fixture\Breadcrumb;

class Test extends TestCase
{
    use Server\Http;
    use Breadcrumbs\Fixture\SystemApplication;
    use Breadcrumbs\Fixture\Assertion;
    use Breadcrumbs\Fixture\Models;

    /**
     * @test
     */
    public function home()
    {
        $this->systemApplicationName('4programmers.org');
        $this->assertThat(
            $this->breadcrumbs('/'),
            $this->equalTo([
                new Breadcrumb('4programmers.org', false),
            ]));
    }

    /**
     * @test
     */
    public function forum()
    {
        $url = $this->newTopic('Watermelon category', 'water-cat', 'Watermelon topic');
        $this->assertThat(
            $this->breadcrumbs($url),
            $this->equalTo([
                new Breadcrumb(
                    '4programmers.net',
                    true,
                    $this->abs('')),
                new Breadcrumb(
                    'Forum',
                    true,
                    $this->abs('/Forum')),
                new Breadcrumb(
                    'Watermelon category',
                    true,
                    $this->abs('/Forum/water-cat')),
                new Breadcrumb(
                    'Watermelon topic',
                    false),
            ]));
    }

    /**
     * @test
     */
    public function job()
    {
        $this->assertThat(
            $this->breadcrumbs('/Praca'),
            $this->equalTo([
                new Breadcrumb('4programmers.net', true, $this->abs('')),
                new Breadcrumb('Praca', false),
            ]));
    }

    private function abs(string $uri): string
    {
        return $this->server->absoluteUrl($uri);
    }
}
