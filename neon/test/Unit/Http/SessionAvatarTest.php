<?php
namespace Neon\Test\Unit\Http;

use Coyote\User;
use Illuminate\Auth\AuthManager;
use Neon\Test\BaseFixture;
use Neon\Test\BaseFixture\Selector\Selector;
use Neon\Test\BaseFixture\View\ViewDom;
use PHPUnit\Framework\TestCase;
use Tests\Integration\BaseFixture\Server;

class SessionAvatarTest extends TestCase
{
    use Server\Laravel\Application;
    use Server\Http;
    use BaseFixture\PublicImageUrl;

    /**
     * @test
     */
    public function userLoggedIn(): void
    {
        $this->publicImageBaseUrl('http://cdn.com/public');
        $this->loggedInUserWithAvatar('foo.png');
        $this->assertSame(
            'http://cdn.com/public/foo.png',
            $this->renderedAvatarUrl());
    }

    /**
     * @test
     */
    public function userLoggedInNoAvatar(): void
    {
        $this->loggedInUser();
        $this->assertSame(
            '/neon/avatarPlaceholder.png',
            $this->renderedAvatarUrl());
    }

    /**
     * @test
     */
    public function controlsUserGuest(): void
    {
        $this->assertSame(['Utwórz konto', 'Logowanie'], $this->controls());
    }

    /**
     * @test
     */
    public function noControlsUserWithAvatar(): void
    {
        $this->loggedInUserWithAvatar();
        $this->assertSame([], $this->controls());
    }

    /**
     * @test
     */
    public function noControlsUserWithoutAvatar(): void
    {
        $this->loggedInUser();
        $this->assertSame([], $this->controls());
    }

    private function loggedInUserWithAvatar(string $avatarUrl = null): User
    {
        $user = $this->loggedInUser();
        $user->photo->setFilename($avatarUrl ?? '');
        return $user;
    }

    private function loggedInUser(): User
    {
        $user = new User();
        $user->name = \uniqid();
        $user->email = '';
        /** @var AuthManager $auth */
        $auth = $this->laravel->app->get(AuthManager::class);
        $auth->login($user);
        return $user;
    }

    private function controls(): array
    {
        $dom = new ViewDom($this->htmlView('/events'));
        $selector = new Selector('header', '.controls', 'a', 'text()');
        return $dom->findStrings($selector->xPath());
    }

    private function renderedAvatarUrl(): string
    {
        $dom = new ViewDom($this->htmlView('/events'));
        $selector = new Selector('header', '#userAvatar', '@src');
        return $dom->findString($selector->xPath());
    }

    private function htmlView(string $uri): string
    {
        return $this->server->get($uri)
            ->assertSuccessful()
            ->getContent();
    }
}
