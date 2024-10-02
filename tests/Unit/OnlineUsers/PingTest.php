<?php
namespace Tests\Unit\OnlineUsers;

use Coyote\Repositories\Redis\SessionRepository;
use Coyote\Services\Session\Handler;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;

class PingTest extends TestCase
{
    use BaseFixture\Server\Http;

    #[Test]
    public function pingIsSavedAsReferer(): void
    {
        $this->request('/ping', 'http://localhost:8880/Mikroblogi');
        $this->assertSame('/Mikroblogi', $this->sessionPath());
    }

    #[Test]
    public function regularPageIsSavedAsItself(): void
    {
        $this->request('/Mikroblogi', 'http://localhost:8880/Other');
        $this->assertSame('/Mikroblogi', $this->sessionPath());
    }

    private function sessionPath(): string
    {
        /** @var Handler $handler */
        $handler = $this->laravel->app->get(Handler::class);
        /** @var SessionRepository $session */
        $session = $this->laravel->app->get(SessionRepository::class);
        $session->destroy(sessionId:1);
        $handler->write(1, \serialize([]));
        return \unserialize($session->get(sessionId:1))['path'];
    }

    private function request(string $uri, string $httpReferer): void
    {
        $this->server->get($uri);
        $this->spoofRequestHeader('Referer', $httpReferer);
    }

    private function spoofRequestHeader(string $headerName, string $value): void
    {
        /** @var Request $request */
        $request = $this->laravel->app->get(Request::class);
        $request->headers->set($headerName, $value);
    }
}
