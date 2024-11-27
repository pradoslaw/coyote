<?php
namespace Neon\Test\Unit\Theme;

use Neon\Application;
use Neon\Test\BaseFixture\Domain\TestApplication;
use Neon\Test\BaseFixture\View\ViewDom;
use PHPUnit\Framework\TestCase;
use Tests\Integration\BaseFixture;

class ThemeSsrTest extends TestCase
{
    use BaseFixture\Server\Laravel\Application;
    use BaseFixture\Server\Http;

    /**
     * @test
     */
    public function lightThemeBody(): void
    {
        $this->setDarkTheme(false);
        $this->assertContains(
            'bg-[#f0f2f5]',
            $this->bodyCssClasses(),
            'Failed asserting that theme is light.');
    }

    /**
     * @test
     */
    public function darkThemeBody(): void
    {
        $this->setDarkTheme(true);
        $this->assertContains(
            'bg-[#0f0f0f]',
            $this->bodyCssClasses(),
            'Failed asserting that theme is dark.');
    }

    /**
     * @test
     */
    public function lightThemeText(): void
    {
        $this->setDarkTheme(false);
        $this->assertContains(
            'text-black',
            $this->textCssClasses(),
            'Failed asserting that theme is light.');
    }

    /**
     * @test
     */
    public function darkThemeText(): void
    {
        $this->setDarkTheme(true);
        $this->assertContains(
            'text-[#eeeeee]',
            $this->textCssClasses(),
            'Failed asserting that theme is dark.');
    }

    private function setDarkTheme(bool $darkTheme): void
    {
        $this->laravel->app->instance(Application::class, TestApplication::application(darkTheme:$darkTheme));
    }

    private function dom(string $uri): ViewDom
    {
        return new ViewDom($this->server->get($uri)->assertSuccessful()->getContent());
    }

    private function textCssClasses(): array
    {
        return \explode(' ', $this->view()->findString('/html/body//main//h1/@class'));
    }

    private function bodyCssClasses(): array
    {
        return \explode(' ', $this->view()->findString('/html/body/@class'));
    }

    private function view(): ViewDom
    {
        return $this->dom('/events');
    }
}
