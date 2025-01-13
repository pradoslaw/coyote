<?php
namespace Tests\Legacy\IntegrationNew\Breadcrumbs;

use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture\Server;
use Tests\Legacy\IntegrationNew\Breadcrumbs;
use Tests\Legacy\IntegrationNew\Breadcrumbs\Fixture\Breadcrumb;

class Test extends TestCase
{
    use Server\Http;
    use Breadcrumbs\Fixture\BreadcrumbView;
    use Breadcrumbs\Fixture\Models;

    /**
     * @test
     */
    public function home()
    {
        $this->assertSame([], $this->breadcrumbs('/'));
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
                    true,
                    $this->abs($url)),
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

    /**
     * @test
     */
    public function breadcrumbContainer()
    {
        $this->assertTrue($this->breadcrumbsContainerVisible('/Forum'));
    }

    /**
     * @test
     */
    public function breadcrumbContainerHomepage()
    {
        $this->assertFalse($this->breadcrumbsContainerVisible('/'));
    }

    private function abs(string $uri): string
    {
        return $this->server->absoluteUrl($uri);
    }
}
