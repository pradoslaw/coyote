<?php
namespace Neon\Test\Unit;

use Neon\Test\BaseFixture\Selector\Selector;
use Neon\Test\BaseFixture\View\ViewDom;
use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;

class ApplicationTest extends TestCase
{
    use BaseFixture\Server\Http;

    /**
     * @test
     */
    public function sectionTitle(): void
    {
        $dom = $this->dom('/events');
        $this->assertSame(
            'Incoming events',
            $dom->find('/html/body/h1/text()'));
    }

    /**
     * @test
     */
    public function navigationItems(): void
    {
        $this->assertSame(
            ['Forum', 'Microblogs', 'Jobs', 'Wiki'],
            $this->findMany('html', 'body', 'header', 'nav', 'ul', 'li'));
    }

    /**
     * @test
     */
    public function githubTitle(): void
    {
        $this->assertSame(
            'Coyote',
            $this->find('html', 'body', 'header', '.github'));
    }

    /**
     * @test
     */
    public function githubStars(): void
    {
        $this->assertSame(
            '?',
            $this->find('html', 'body', 'header', '.github', '.stars'));
    }

    /**
     * @test
     */
    public function headerControls(): void
    {
        $this->assertSame(
            ['Register', 'Login'],
            $this->findMany('html', 'body', 'header', 'ul', 'li'));
    }

    private function findMany(string...$selectors): array
    {
        $selector = new Selector(...$selectors);
        return $this->dom('/events')
            ->findMany($selector->xPath());
    }

    private function find(string...$selectors): string
    {
        $selector = new Selector(...$selectors);
        return $this->dom('/events')
            ->find($selector->xPath());
    }

    private function dom(string $uri): ViewDom
    {
        return new ViewDom($this->server->get($uri)->assertSuccessful()->getContent());
    }
}
