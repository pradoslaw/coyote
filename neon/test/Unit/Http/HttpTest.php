<?php
namespace Neon\Test\Unit\Http;

use Neon\Application;
use Neon\Test\BaseFixture\NoEvents;
use Neon\Test\BaseFixture\NoJobOffers;
use Neon\Test\BaseFixture\NoneAttendance;
use Neon\Test\BaseFixture\View\ViewDom;
use Neon\Test\Unit\Navigation\Fixture\LoggedInUser;
use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;

class HttpTest extends TestCase
{
    use BaseFixture\Server\Laravel\Application;
    use BaseFixture\Server\Http;

    /**
     * @test
     */
    public function applicationBreadcrumbName(): void
    {
        $dom = $this->dom('/events');
        $this->assertSame(
            '4programmers.net',
            $dom->findString('//main//nav/ul/li[1]/text()'));
    }

    /**
     * @test
     */
    public function applicationTitle(): void
    {
        $this->setApplicationTitle('Ours is the fury');
        $dom = $this->dom('/events');
        $this->assertSame(
            'Ours is the fury',
            $dom->findString('/html/head/title/text()'));
    }

    private function setApplicationTitle(string $applicationName): void
    {
        $this->laravel->app->instance(Application::class,
            new Application($applicationName,
                new NoneAttendance(),
                new NoJobOffers(),
                new NoEvents(),
                LoggedInUser::guest()));
    }

    private function dom(string $uri): ViewDom
    {
        return new ViewDom($this->server->get($uri)->assertSuccessful()->getContent());
    }
}
