<?php
namespace Neon\Test\Unit\Navigation;

use Neon\Application;
use Neon\Test\BaseFixture\NoneAttendance;
use Neon\Test\BaseFixture\Selector\Selector;
use Neon\Test\BaseFixture\View\ViewDom;
use Neon\Test\Unit\Navigation\Fixture\LoggedInUser;
use PHPUnit\Framework\TestCase;

class AvatarTest extends TestCase
{
    /**
     * @test
     */
    public function loggedInUser(): void
    {
        $this->assertSame(
            'face.png',
            $this->renderedAvatarUrl(LoggedInUser::withAvatar('face.png')));
    }

    /**
     * @test
     */
    public function guest(): void
    {
        $this->assertSame(
            '/neon/avatarPlaceholder.png',
            $this->renderedAvatarUrl(LoggedInUser::guest()));
    }

    private function renderedAvatarUrl(LoggedInUser $visitor): string
    {
        $application = new Application('', new NoneAttendance(), $visitor);
        return $this->text($application, new Selector('header', '#userAvatar', '@src'));
    }

    private function text(Application $application, Selector $selector): string
    {
        $dom = new ViewDom($application->html());
        return $dom->find($selector->xPath());
    }
}
