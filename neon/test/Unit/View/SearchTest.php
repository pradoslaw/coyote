<?php
namespace Neon\Test\Unit\View;

use Neon\Test\BaseFixture\WebClient;
use PHPUnit\Framework\TestCase;
use Tests\Integration\BaseFixture;

class SearchTest extends TestCase
{
    use BaseFixture\Server\Laravel\Application;

    /**
     * @test
     */
    public function test(): void
    {
        $url = $this->searchAndGetRedirectionUrl(
            '/events',
            '#search-bar input',
            'foo bar');

        $this->assertSame('http://nginx/Search?q=foo+bar', $url);
    }

    private function searchAndGetRedirectionUrl(string $url, string $cssSelector, string $value): ?string
    {
        $client = new WebClient();
        $client->navigateTo('http://nginx' . $url);
        $client->typeAndSubmit($cssSelector, $value);
        $url = $client->currentUrl();
        $client->close();
        return $url;
    }
}
